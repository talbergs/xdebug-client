<?php

function pretty_xml($xml): string {
    if (is_string($xml)) {
        $xml = simplexml_load_string($xml);
    }
    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->formatOutput = true;
    return $dom->saveXML();
}
