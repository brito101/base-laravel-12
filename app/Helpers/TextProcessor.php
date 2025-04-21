<?php

namespace App\Helpers;

use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Str;

class TextProcessor
{
    public static function store(string $title, string $package, string $text = '', bool $xss = false): string
    {
        $text = preg_replace('/[\x{34F}\x{AD}\x{200E}]/u', '', $text);
        $description = str_replace(["\'<?xml encoding=\"utf-8\" ?>\'", '<!--?xml encoding="utf-8" ?-->'], ['', ''], $text);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$description, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        $imageFile = $dom->getElementsByTagName('img');

        foreach ($imageFile as $item => $image) {
            $img = $image->getAttribute('src');

            // XSS prevention
            $image->removeAttribute('onerror');

            if (! filter_var($img, FILTER_VALIDATE_URL)) {
                if (array_key_exists(1, explode(';', $img))) {
                    [, $img] = explode(';', $img);
                }

                if (array_key_exists(1, explode(',', $img))) {
                    [, $img] = explode(',', $img);
                }

                if ($img && self::check_base64_image($img)) {

                    $imageData = base64_decode($img);
                    $image_name = Str::slug($title).'-'.time().$item.'.png';

                    $destinationPath = storage_path().'/app/public/'.$package.'/text';
                    if (! file_exists($destinationPath)) {
                        mkdir($destinationPath, 755, true);
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
        }

        $description = $dom->saveHTML();

        if ($description == "<p><br></p>\n") {
            $description = '';
        }

        $url = str_replace('www.', '', env('APP_URL'));

        return str_replace([$url, env('APP_URL')], ['', ''], $description);
    }

    public static function urlImageTransform($text, bool $misc = false): string
    {
        if ($misc) {
            $text = substr($text, 0, strpos($text, '<p><b>3. A')).'</div>';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        $imageFile = $dom->getElementsByTagName('img');

        $url = str_replace('www.', '', env('APP_URL'));
        foreach ($imageFile as $image) {
            $img = str_replace([$url, env('APP_URL')], ['', ''], $image->getAttribute('src'));

            try {
                $image->setAttribute('src', 'data:image/svg+xml;base64,'.base64_encode(file_get_contents(public_path($img))));
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

    private static function check_base64_image($s): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
    }
}
