<?php
namespace SRU;

class Record
{
    public $node;
    protected $doc;
    protected $xpath;
    protected $recordPacking;
    protected $recordSchema;
    protected $recordPosition;
    protected $recordData;

    public function __construct(\DOMDocument $doc, \DOMNode $node)
    {
        $this->node = $node;
        $this->doc = $doc;
        $this->xpath = new \DOMXPath($this->doc);
        $this->xpath->registerNamespace("zs", "http://www.loc.gov/zing/srw/");
    }

    public function packing()
    {
        if(!$this->recordPacking) {
            $nodes = $this->xpath->query("./zs:recordPacking", $this->node);
            foreach($nodes as $node) {
                $this->recordPacking = $node->nodeValue;
            }
        }
        return($this->recordPacking);
    }

    public function schema()
    {
        if(!$this->recordSchema) {
            $nodes = $this->xpath->query("./zs:recordSchema", $this->node);
            foreach($nodes as $node) {
                $this->recordSchema = $node->nodeValue;
            }
        }
        return($this->recordSchema);
    }

    public function position()
    {
        if(!$this->recordPosition) {
            $nodes = $this->xpath->query("./zs:recordPosition", $this->node);
            foreach($nodes as $node) {
                $this->recordPosition = $node->nodeValue;
            }
        }
        return($this->recordPosition);
    }

    public function data($raw=false)
    {
        if(!$this->recordData) {
            $nodes = $this->xpath->query("./zs:recordData", $this->node);
            foreach($nodes as $node) {
                $this->recordData = $node->firstChild;
            }
        }
        if($raw && $this->packing() == "xml") {
            $data = $this->doc->saveXML($this->recordData);
        } else {
            $data = $this->recordData;
        }

        return($data);
    }
}
?>