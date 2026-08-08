<?php

namespace App\Helpers;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;
use Exception;
use Illuminate\Support\Str;

class TextProcessor
{
    /**
     * Entidades HTML estruturais que precisam continuar escapadas mesmo
     * apos o decode de acentos feito no retorno de store() - decodificar
     * estas quebraria a marcacao (um "&lt;" solto no meio do texto seria
     * interpretado como inicio de tag ao renderizar).
     */
    private const PROTECTED_ENTITIES = [
        '&amp;' => '@@TP_AMP@@',
        '&lt;' => '@@TP_LT@@',
        '&gt;' => '@@TP_GT@@',
        '&quot;' => '@@TP_QUOT@@',
        '&#039;' => '@@TP_APOS@@',
        '&apos;' => '@@TP_APOS@@',
    ];

    /**
     * Table tags Dompdf cannot lay out when they sit outside a <table>.
     */
    private const TABLE_PART_TAGS = ['tbody', 'thead', 'tfoot', 'tr', 'td', 'th'];

    public static function store(string $title, string $package, string $text = '', bool $xss = false): string
    {
        $text = preg_replace('/[\x{34F}\x{AD}\x{200E}]/u', '', $text);
        /* Remove artefatos de processamentos anteriores (a versao antiga deste
        metodo deixava a declaracao xml de encoding vazar para o valor
        salvo, que se acumulava a cada edicao subsequente). */
        $description = preg_replace('/<\?xml[^>]*\?>/i', '', $text);
        $dom = new DOMDocument('1.0', 'UTF-8');
        /* O prefixo com a declaracao xml de encoding e a forma padrao de
        sinalizar UTF-8 para o parser HTML do libxml2 - sem ele, bytes
        UTF-8 multibyte (acentos) sao interpretados como Latin-1 e
        corrompidos. E removido do documento antes do saveHTML(), mais
        abaixo, para nao vazar para o valor salvo (bug da versao anterior
        deste metodo). */
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$description, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);

        if ($dom->firstChild instanceof \DOMProcessingInstruction) {
            $dom->removeChild($dom->firstChild);
        }

        // XSS prevention: strip event handler attributes (onerror, onload, onclick, ...)
        // from every element, not just <img onerror>.
        self::stripEventHandlerAttributes($dom);

        $imageFile = $dom->getElementsByTagName('img');

        foreach ($imageFile as $item => $image) {
            $img = $image->getAttribute('src');
            $extension = self::extensionFromDataUri($img);

            if (! filter_var($img, FILTER_VALIDATE_URL)) {
                if (array_key_exists(1, explode(';', $img))) {
                    [, $img] = explode(';', $img);
                }

                if (array_key_exists(1, explode(',', $img))) {
                    [, $img] = explode(',', $img);
                }

                if ($img && self::check_base64_image($img)) {

                    $imageData = base64_decode($img);
                    $image_name = Str::slug($title).'-'.time().$item.'.'.$extension;

                    $destinationPath = storage_path().'/app/public/'.$package.'/text';
                    if (! file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    $path = $destinationPath.'/'.$image_name;
                    file_put_contents($path, $imageData);
                    $image->removeAttribute('src');
                    $image->removeAttribute('data-filename');

                    $image->setAttribute('alt', $title);
                    $oldStyle = $image->getAttribute('style');
                    if ($oldStyle) {
                        $image->setAttribute('style', $oldStyle.' max-width: 100%;');
                    } else {
                        $image->setAttribute('style', 'max-width: 100%;');
                    }
                    $image->setAttribute('src', '/storage/'.$package.'/text/'.$image_name);
                }
            }
        }

        $elements = $dom->getElementsByTagName('pre');

        for ($i = $elements->length - 1; $i >= 0; $i--) {
            $nodePre = $elements->item($i);
            try {
                $nodeDiv = $dom->createElement('code', $nodePre->nodeValue);
                $nodePre->parentNode->replaceChild($nodeDiv, $nodePre);
            } catch (Exception) {
                continue;
            }
        }

        if (! $xss) {
            $xss = $dom->getElementsByTagName('script');
            foreach ($xss as $e) {
                $e->parentNode->removeChild($e);
            }

            $meta = $dom->getElementsByTagName('meta');
            foreach ($meta as $e) {
                $e->parentNode->removeChild($e);
            }
        }

        /* DOMDocument::saveHTML() converte acentos para entidades HTML
         nomeadas mesmo em documento UTF-8 - decodifica de volta para
         caracteres normais, preservando as entidades estruturais
         (&lt; &gt; &amp; etc.) para nao quebrar a marcação. */
        $html = str_replace(array_keys(self::PROTECTED_ENTITIES), array_values(self::PROTECTED_ENTITIES), $dom->saveHTML());
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        self::repairTableFragments($dom);

        $description = str_replace(array_values(self::PROTECTED_ENTITIES), array_keys(self::PROTECTED_ENTITIES), $html);

        if ($description == "<p><br></p>\n") {
            $description = '';
        }

        $url = str_replace('www.', '', env('APP_URL'));

        return str_replace([$url, env('APP_URL')], ['', ''], $description);
    }

    public static function urlImageTransform($text, bool $misc = false): string
    {
        if ($misc) {
            $cutPosition = strpos($text, '<p><b>3. A');
            if ($cutPosition !== false) {
                $text = substr($text, 0, $cutPosition).'</div>';
            }
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        $imageFile = $dom->getElementsByTagName('img');

        $url = str_replace('www.', '', env('APP_URL'));
        foreach ($imageFile as $image) {
            $img = str_replace([$url, env('APP_URL')], ['', ''], $image->getAttribute('src'));
            $imagePath = public_path($img);

            if (! is_file($imagePath)) {
                $image->parentNode->removeChild($image);

                continue;
            }

            try {
                $image->setAttribute('src', 'data:image/svg+xml;base64,'.base64_encode(file_get_contents($imagePath)));
                $oldStyle = $image->getAttribute('style');
                if ($oldStyle) {
                    $image->setAttribute('style', $oldStyle.' max-width: 100%; height: auto;');
                } else {
                    $image->setAttribute('style', 'max-width: 100%; height: auto;');
                }
            } catch (Exception) {
                $image->parentNode->removeChild($image);
            }
        }

        if ($misc) {
            $xpath = new DOMXPath($dom);
            foreach ($xpath->query('//div[contains(attribute::class, "remove-misc")]') as $e) {
                $e->parentNode->removeChild($e);
            }
        }

        return $dom->saveHTML();
    }

    /**
     * Repair table fragments that lost their enclosing <table>.
     *
     * Rich text pasted into the editor may carry rows/cells — or divs styled as
     * such — without the wrapping table. Browsers drop those tags silently, but
     * Dompdf aborts the whole render with "Parent table not found for table cell".
     */
    private static function repairTableFragments(DOMDocument $dom): void
    {
        $xpath = new DOMXPath($dom);

        // Drop table display values with no table box above them. Walking in
        // document order fixes an unanchored parent before its children are
        // checked, so a stripped parent no longer vouches for its descendants.
        foreach ($xpath->query('//*[@style]') as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $style = $node->getAttribute('style');
            $cleaned = preg_replace('/display\s*:\s*table-(row-group|header-group|footer-group|row|cell)\s*;?/i', '', $style);

            if ($cleaned === $style || self::hasTableContext($node)) {
                continue;
            }

            if (trim($cleaned) === '') {
                $node->removeAttribute('style');
            } else {
                $node->setAttribute('style', trim($cleaned));
            }
        }

        // Wrap orphan rows/cells in a synthetic table so they keep their tabular
        // rendering. Dompdf fills in the missing row groups and rows by itself.
        $query = implode(' | ', array_map(
            static fn (string $tag): string => '//'.$tag,
            self::TABLE_PART_TAGS
        ));

        while (($orphan = self::firstOrphanTablePart($xpath, $query)) !== null) {
            $table = $dom->createElement('table');
            $orphan->parentNode->replaceChild($table, $orphan);
            $table->appendChild($orphan);
            self::absorbFollowingTableParts($table);
        }
    }

    /**
     * Check whether an enclosing element establishes a table box for the node.
     */
    private static function hasTableContext(DOMNode $node): bool
    {
        for ($parent = $node->parentNode; $parent instanceof DOMElement; $parent = $parent->parentNode) {
            if (strtolower($parent->nodeName) === 'table') {
                return true;
            }

            if (preg_match('/display\s*:\s*(inline-)?table/i', $parent->getAttribute('style'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find the outermost table part still missing its table, in document order.
     */
    private static function firstOrphanTablePart(DOMXPath $xpath, string $query): ?DOMElement
    {
        foreach ($xpath->query($query) as $node) {
            if ($node instanceof DOMElement && ! self::hasTableContext($node)) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Pull the sibling table parts that followed the orphan into the same table.
     */
    private static function absorbFollowingTableParts(DOMElement $table): void
    {
        while (($sibling = $table->nextSibling) !== null) {
            $isTablePart = $sibling instanceof DOMElement
                && in_array(strtolower($sibling->nodeName), self::TABLE_PART_TAGS, true);
            $isBlankText = $sibling instanceof DOMText && trim($sibling->wholeText) === '';

            if (! $isTablePart && ! $isBlankText) {
                break;
            }

            $table->appendChild($sibling);
        }
    }

    private static function check_base64_image($s): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
    }

    /**
     * Strip event handler attributes (onerror, onload, onclick, ...) from every
     * element in the document, not just onerror on <img>.
     */
    private static function stripEventHandlerAttributes(DOMDocument $dom): void
    {
        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//*[@*]') as $element) {
            if (! $element instanceof DOMElement) {
                continue;
            }

            foreach (iterator_to_array($element->attributes) as $attribute) {
                if (str_starts_with(strtolower($attribute->nodeName), 'on')) {
                    $element->removeAttribute($attribute->nodeName);
                }
            }
        }
    }

    /**
     * Guess a file extension from a base64 data URI's declared mime type, so
     * saved images keep a format that matches their actual bytes.
     */
    private static function extensionFromDataUri(string $dataUri): string
    {
        if (! preg_match('#^data:image/([a-zA-Z0-9.+-]+);base64,#i', $dataUri, $matches)) {
            return 'png';
        }

        return match (strtolower($matches[1])) {
            'jpeg', 'jpg' => 'jpg',
            'gif' => 'gif',
            'webp' => 'webp',
            'svg+xml' => 'svg',
            default => 'png',
        };
    }
}
