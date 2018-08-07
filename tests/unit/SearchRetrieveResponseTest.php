<?php
namespace SRU\tests;

use PHPUnit\Framework\TestCase;

class SearchRetrieveResponseTest extends TestCase
{
    public function testParseValidResponse()
    {
        $responseXml = file_get_contents(__DIR__ . '/fixtures/record_packing_xml.xml');
        $searchResponse = new \SRU\SearchRetrieveResponse($responseXml);
        $this->assertEquals(3212, $searchResponse->numberOfRecords());
        $this->assertEquals('11', $searchResponse->nextRecordPosition());
        $this->assertEquals(10, count($searchResponse->getRecords()));
        for ($i = 0; $i < 10; $i++) {
            $this->assertInstanceOf('\SRU\Record', $searchResponse->getRecords()[$i]);
        }
    }
    public function testParseInvalidResponse()
    {
        $responseXml = file_get_contents(__DIR__ . '/fixtures/invalid_marcxml.xml');
        $searchResponse = new \SRU\SearchRetrieveResponse($responseXml);
        $this->assertEquals(1, $searchResponse->numberOfRecords());
        $this->assertNull($searchResponse->nextRecordPosition());
        $this->assertEquals(1, count($searchResponse->getRecords()));
        $this->assertInstanceOf('\SRU\Record', $searchResponse->getRecords()[0]);
    }       
}
