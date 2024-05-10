<?php
namespace SRU\tests;

use PHPUnit\Framework\TestCase;

class RecordTest extends TestCase
{
    public function testParseValidXmlPackingResponse()
    {
        $responseXml = file_get_contents(__DIR__ . '/fixtures/record_packing_xml.xml');
        $searchResponse = new \SRU\SearchRetrieveResponse($responseXml);
        for ($i = 0; $i < 10; $i++) {
            $record = $searchResponse->getRecords()[$i];
            $this->assertInstanceOf('\SRU\Record', $record);
            $this->assertEquals('xml', $record->packing());
            $this->assertEquals('dc', $record->schema());
            $this->assertEquals($i + 1, $record->position());
            $data = $record->data();
            $this->assertInstanceOf('\DOMElement', $data);
            $this->assertEquals('srw_dc:dc', $data->tagName);
            $this->assertEquals(
                'info:srw/schema/1/dc-schema',
                $data->lookupNamespaceURI('srw_dc')
            );
            $rawData = $record->data(true);
            $this->assertTrue(is_string($rawData));
            $this->assertStringContainsString(
                '<srw_dc:dc xmlns:srw_dc="info:srw/schema/1/dc-schema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="info:srw/schema/1/dc-schema http://www.loc.gov/standards/sru/resources/dc-schema.xsd">',
                $rawData
            );
        }
    }

    public function testParseValidStringPackingResponse()
    {
        $responseXml = file_get_contents(__DIR__ . '/fixtures/record_packing_string.xml');
        $searchResponse = new \SRU\SearchRetrieveResponse($responseXml);
        for ($i = 0; $i < 10; $i++) {
            $record = $searchResponse->getRecords()[$i];
            $this->assertInstanceOf('\SRU\Record', $record);
            $this->assertEquals('string', $record->packing());
            $this->assertEquals('dc', $record->schema());
            $this->assertEquals($i + 1, $record->position());
            $data = $record->data();
            $this->assertInstanceOf('\DOMText', $data);
            $this->assertStringContainsString(
                '<srw_dc:dc xmlns:srw_dc="info:srw/schema/1/dc-schema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="info:srw/schema/1/dc-schema http://www.loc.gov/standards/sru/resources/dc-schema.xsd">',
                $data->wholeText
            );

            $rawData = $record->data(true);
            $this->assertTrue(is_string($rawData));
            $this->assertStringContainsString(
                '<srw_dc:dc xmlns:srw_dc="info:srw/schema/1/dc-schema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="info:srw/schema/1/dc-schema http://www.loc.gov/standards/sru/resources/dc-schema.xsd">',
                $rawData
            );
        }
    }

    public function testParseValidMarcXmlResponse()
    {
        $responseXml = file_get_contents(__DIR__ . '/fixtures/valid_marcxml.xml');
        $searchResponse = new \SRU\SearchRetrieveResponse($responseXml);
        for ($i = 0; $i < 10; $i++) {
            $record = $searchResponse->getRecords()[$i];
            $this->assertInstanceOf('\SRU\Record', $record);
            $this->assertEquals('xml', $record->packing());
            $this->assertEquals('marcxml', $record->schema());
            $this->assertEquals($i + 1, $record->position());
            $data = $record->data();
            $this->assertInstanceOf('\DOMElement', $data);
            $this->assertEquals('record', $data->tagName);
            $this->assertEquals(
                'http://www.loc.gov/MARC21/slim',
                $data->namespaceURI
            );
            $this->assertTrue($data->isDefaultNamespace('http://www.loc.gov/MARC21/slim'));
            $rawData = $record->data(true);
            $this->assertTrue(is_string($rawData));
            $this->assertStringContainsString(
                '<record xmlns="http://www.loc.gov/MARC21/slim">',
                $rawData
            );
            $r = new \File_MARCXML($rawData, \File_MARC::SOURCE_STRING);
            $recordsParsed = 0;
            // @var $rec \File_MARC_Record
            while ($rec = $r->next()) {
                $recordsParsed++;
                $this->assertNotEmpty($rec->getLeader());
                $this->assertGreaterThan(0, count($rec->getFields()));
            }
            $this->assertEquals(1, $recordsParsed);
        }
    }

    public function testParseInvalidResponse()
    {
        $responseXml = file_get_contents(__DIR__ . '/fixtures/invalid_marcxml.xml');
        $searchResponse = new \SRU\SearchRetrieveResponse($responseXml);

        $record = $searchResponse->getRecords()[0];
        $this->assertInstanceOf('\SRU\Record', $record);
        $this->assertEquals('xml', $record->packing());
        $this->assertEquals('info:srw/schema/1/marcxml-v1.1', $record->schema());
        $this->assertEquals(1, $record->position());
        $data = $record->data();
        $this->assertInstanceOf('\DOMElement', $data);
        $this->assertEquals('recordData', $data->tagName);
        $this->assertEmpty($data->namespaceURI);
        $this->assertFalse($data->isDefaultNamespace('http://www.loc.gov/MARC21/slim'));
        $rawData = $record->data(true);
        $this->assertTrue(is_string($rawData));
        $this->assertStringContainsString(
            '<recordData><leader>00953nam  2200289   4500</leader>',
            $rawData
        );
        $rawData = str_replace('<recordData>', '<record xmlns="http://www.loc.gov/MARC21/slim">', $rawData);
        $rawData = str_replace('</recordData>', '</record>', $rawData);
        $r = new \File_MARCXML($rawData, \File_MARC::SOURCE_STRING);
        $recordsParsed = 0;
        // @var $rec \File_MARC_Record
        while ($rec = $r->next()) {
            $recordsParsed++;
            $this->assertNotEmpty($rec->getLeader());
            $this->assertCount(44, $rec->getFields());
        }
        $this->assertEquals(1, $recordsParsed);
    }
}
