<?php

class App
{
    public function processRequest(string $req)
    {
        $xml = simplexml_load_string($req);

        foreach ($xml->getDocNamespaces() as $prefix => $ns) {
            $xml->registerXPathNamespace($prefix ?: 'a', $ns);
        }

        switch ($xml->getName()) {
        case 'init': return InitRequest::fromXML($xml);
        default: throw new \Exception("{$xml->getName()} < unrecognized request name.");
        }
    }
}
