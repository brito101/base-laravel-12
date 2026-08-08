<?php

use App\Helpers\TextProcessor;
use Illuminate\Support\Facades\File;

afterEach(function () {
    File::deleteDirectory(storage_path('app/public/test-text-processor'));
});

it('does not leak the xml encoding declaration and keeps accented characters', function () {
    $result = TextProcessor::store('Título', 'test-text-processor', '<p>Café com açúcar</p>');

    expect($result)->not->toContain('<?xml')
        ->and($result)->toContain('Café com açúcar');
});

it('saves base64 images with an extension matching their mime type', function () {
    $html = '<p><img src="data:image/jpeg;base64,'.base64_encode('fake-jpeg-bytes').'"></p>';

    TextProcessor::store('My Title', 'test-text-processor', $html);

    $destination = storage_path('app/public/test-text-processor/text');
    $files = File::files($destination);

    expect($files)->toHaveCount(1)
        ->and($files[0]->getExtension())->toBe('jpg');
});

it('creates the upload directory with a readable owner permission', function () {
    $html = '<p><img src="data:image/png;base64,'.base64_encode('fake-png-bytes').'"></p>';

    TextProcessor::store('My Title', 'test-text-processor', $html);

    $destination = storage_path('app/public/test-text-processor/text');

    // A permissao 0755 (octal) precisa deixar o bit de leitura do dono ligado.
    // O bug antigo passava 755 (decimal) para o mkdir, que zera esse bit.
    expect(fileperms($destination) & 0400)->toBeGreaterThan(0);
});

it('strips script tags and event handler attributes from any element', function () {
    $html = '<div onclick="alert(1)"><script>alert(2)</script>'
        .'<img src="x.png" onerror="alert(3)" onload="alert(4)">'
        .'<p onmouseover="alert(5)">hi</p></div>';

    $result = TextProcessor::store('Title', 'test-text-processor', $html);

    expect($result)->not->toContain('<script')
        ->and($result)->not->toContain('onclick')
        ->and($result)->not->toContain('onerror')
        ->and($result)->not->toContain('onload')
        ->and($result)->not->toContain('onmouseover');
});

it('keeps the original text when the misc marker is not present', function () {
    $text = '<p>Some content without the marker</p>';

    $result = TextProcessor::urlImageTransform($text, true);

    expect($result)->toContain('Some content without the marker');
});

it('removes an img tag when the referenced file does not exist', function () {
    $html = '<p><img src="/does-not-exist-'.uniqid().'.png"></p>';

    $result = TextProcessor::urlImageTransform($html);

    expect($result)->not->toContain('<img');
});
