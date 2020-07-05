<?php

function pretty_xml($xml): string {
    if (is_string($xml)) {
        $xml = simplexml_load_string($xml);
    }
    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->formatOutput = true;

    return $dom->saveXML();
}

function base_path(string $path): string {
    return __DIR__ . '/' . ltrim($path, '/');
}

function public_path(string $path): string {
    return base_path('public/' . ltrim($path, '/'));
}

function info(string $text) {
    echo "\033[33m${text}\033[0m\n";
}

function debug(string $text) {
    echo "\033[31m${text}\033[0m\n";
}
