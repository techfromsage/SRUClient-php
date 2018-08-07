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

    /**
     * Record constructor
     *
     * @param \DOMDocument $doc  SRU SearchRetrieve response DOMDocument
     * @param \DOMNode     $node SearchRetrieve result record
     */
    public function __construct(\DOMDocument $doc, \DOMNode $node)
    {
        $this->node = $node;
        $this->doc = $doc;
        $this->xpath = new \DOMXPath($this->doc);
        $this->xpath->registerNamespace('zs', 'http://www.loc.gov/zing/srw/');
    }

    /**
     * Returns the SRU 'recordPacking' value, which is either 'string' or 'xml'
     *
     * @return string|null
     */
    public function packing()
    {
        if (!$this->recordPacking) {
            $nodes = $this->xpath->query('./zs:recordPacking', $this->node);
            foreach ($nodes as $node) {
                $this->recordPacking = $node->nodeValue;
            }
        }
        return $this->recordPacking;
    }

    /**
     * Returns the SRU 'recordSchema' value for the record
     *
     * @return string|null
     */
    public function schema()
    {
        if (!$this->recordSchema) {
            $nodes = $this->xpath->query('./zs:recordSchema', $this->node);
            foreach ($nodes as $node) {
                $this->recordSchema = $node->nodeValue;
            }
        }
        return $this->recordSchema;
    }

    /**
     * Returns the record's position in the result set
     *
     * @return string
     */
    public function position()
    {
        if (!$this->recordPosition) {
            $nodes = $this->xpath->query('./zs:recordPosition', $this->node);
            foreach ($nodes as $node) {
                $this->recordPosition = $node->nodeValue;
            }
        }
        return $this->recordPosition;
    }

    /**
     * Return the recordData contents
     *
     * @param boolean $raw Boolean to return the XML or text record data as text (e.g. an XML string)
     * @return string|\DOMNode|\DOMElement|\DOMText
     */
    public function data($raw = false)
    {
        if (!$this->recordData) {
            $nodes = $this->xpath->query('./zs:recordData', $this->node);
            $valid = true;
            foreach ($nodes as $node) {
                if ($node->childNodes->length === 1) {
                    $this->recordData = $node->firstChild;
                } elseif ($this->packing() === 'xml') {
                    $valid = false;
                    // The zs:recordData looks like it's containing invalid XML with more than one top level node,
                    // so let's create a 'dummy' wrapper node for it for the calling app to have something to work with
                    $recordData = new \DOMElement('recordData');
                    $doc = new \DOMDocument();
                    $doc->appendChild($recordData);
                    for ($i = 0; $i < $node->childNodes->length; $i++) {
                        $importNode = $doc->importNode($node->childNodes->item($i), true);
                        $doc->documentElement->appendChild($importNode);
                    }
                    $this->doc = $doc;
                    $this->recordData = $recordData;
                } else {
                    $this->recordData = new \DOMText();
                    for ($i = 0; $i < $node->childNodes->length; $i++) {
                        $this->recordData->appendData($node->childNodes->item($i)->nodeValue);
                    }
                }
            }
        }
        if ($raw) {
            $data = $this->packing() === 'xml' ? $this->doc->saveXML($this->recordData) : $this->recordData->nodeValue;
        } else {
            $data = $this->recordData;
        }

        return $data;
    }
}
