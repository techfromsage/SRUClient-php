<?php
namespace SRU;

class ScanResponse
{
    public $doc;

    function __construct($xml)
    {
        if(is_string($xml))
        {
            $this->doc = new \DOMDocument();
            $this->doc->loadXML($xml);
        } elseif (is_a($xml, '\DOMDocument'))
        {
            $this->doc = $xml;
        } else {
            throw new \InvalidArgumentException('Argument must be an XML string or DOMDocument object');
        }
    }
}
