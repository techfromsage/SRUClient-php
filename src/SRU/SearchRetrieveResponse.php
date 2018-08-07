<?php
namespace SRU;

class SearchRetrieveResponse
{
    /**
     * @var \DOMDocument
     */
    public $doc;
    /**
     * @var \SRU\Record[]
     */
    protected $records;
    /**
     * @var \DOMXPath
     */
    protected $xpath;
    /**
     * @var int
     */
    protected $numberOfRecords;
    /**
     * @var int
     */
    protected $nextRecordPosition;

    /**
     * @param string|\DOMDocument $xml SearchRetrieve XML response document
     * @throws \InvalidArgumentException If $xml is not a string or DOMDocument
     */
    public function __construct($xml)
    {
        if (is_string($xml)) {
            $this->doc = new \DOMDocument();
            $this->doc->loadXML($xml);
        } elseif (is_a($xml, '\DOMDocument')) {
            $this->doc = $xml;
        } else {
            throw new \InvalidArgumentException(
                'Argument must be an XML string or DOMDocument object'
            );
        }

        $this->xpath = new \DOMXPath($this->doc);
        $this->xpath->registerNamespace('zs', 'http://www.loc.gov/zing/srw/');
    }

    /**
     * @return int
     */
    public function numberOfRecords()
    {
        if (!$this->numberOfRecords) {
            $nodes = $this->xpath->query('/zs:searchRetrieveResponse/zs:numberOfRecords');
            foreach ($nodes as $node) {
                $this->numberOfRecords = (int) $node->nodeValue;
            }
        }
        return $this->numberOfRecords;
    }

    /**
     * @return int
     */
    public function nextRecordPosition()
    {
        if (!$this->nextRecordPosition) {
            $nodes = $this->xpath->query('/zs:searchRetrieveResponse/zs:nextRecordPosition');
            foreach ($nodes as $node) {
                $this->nextRecordPosition = (int) $node->nodeValue;
            }
        }
        return $this->nextRecordPosition;
    }

    /**
     * @return Record[]
     */
    public function getRecords()
    {
        if (!$this->records) {
            $this->addRecords();
        }
        return $this->records;
    }

    /**
     * Parses the document and adds the records from the record elements
     *
     * @return void
     */
    protected function addRecords()
    {
        $this->records = [];
        $nodes = $this->xpath->query('/zs:searchRetrieveResponse/zs:records/zs:record');
        foreach ($nodes as $node) {
            array_push($this->records, new Record($this->doc, $node));
        }
    }
}
