<?php

function pretty_xml($xml) {
    $xml = simplexml_load_string($xml);
    $dom = dom_import_simplexml($xml)->ownerDocument;
    $dom->formatOutput = true;
    echo $dom->saveXML();
}
