<?php

namespace SRU\tests;

use SRU\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @dataProvider explainDataProvider
     */
    public function testExplain(string $method, array $defaults = [])
    {
        $clientOptions = $defaults + ['httpMethod' => $method];
        $client = new Client($this->baseUrl($method), $clientOptions);

        $response = $client->explain(true);

        $expectedArgs = array_merge([
            'operation' => 'explain',
            'version' => '1.1',
        ], $defaults);
        $data = json_decode($response, true);
        $responseKey = $method === 'POST' ? 'form' : 'args';

        $this->assertEquals($expectedArgs, $data[$responseKey]);
    }

    public function explainDataProvider(): iterable
    {
        yield 'GET method' => ['GET'];
        yield 'POST method' => ['POST'];
        yield 'Override default version' => ['GET', ['version' => '2.0']];
    }

    /**
     * @dataProvider searchRetrieveDataProvider
     */
    public function testSearchRetrieve(string $method, array $defaults = [], array $options = [])
    {
        $clientOptions = $defaults + ['httpMethod' => $method];
        $client = new Client($this->baseUrl($method), $clientOptions);

        $response = $client->searchRetrieve('test', $options, true);

        $expectedArgs = array_merge([
            'operation' => 'searchRetrieve',
            'version' => '1.1',
            'maximumRecords' => '10',
            'query' => 'test',
            'recordPacking' => 'xml',
            'startRecord' => '1',
        ], $defaults, $options);
        $data = json_decode($response, true);
        $responseKey = $method === 'POST' ? 'form' : 'args';

        $this->assertEquals($expectedArgs, $data[$responseKey]);
    }

    public function searchRetrieveDataProvider(): iterable
    {
        yield 'GET method' => ['GET'];
        yield 'POST method' => ['POST'];
        yield 'Override default version' => ['GET', ['version' => '2.0']];
        yield 'Override default recordSchema' => ['GET', ['recordSchema' => 'foo']];
        yield 'Override default maximumRecords' => ['GET', ['maximumRecords' => '42']];
        yield 'Call options' => ['GET', ['version' => '2.0'], ['version' => '3.0']];
    }

    /**
     * @dataProvider scanDataProvider
     */
    public function testScan(string $method, array $defaults = [], array $options = [])
    {
        $clientOptions = $defaults + ['httpMethod' => $method];
        $client = new Client($this->baseUrl($method), $clientOptions);

        $response = $client->scan('test', $options, true);

        if (isset($defaults['maximumRecords'])) {
            $defaults['maximumTerms'] = $defaults['maximumRecords'];
            unset($defaults['maximumRecords']);
        }
        $expectedArgs = array_merge([
            'operation' => 'scan',
            'version' => '1.1',
            'maximumTerms' => '10',
            'scanClause' => 'test',
        ], $defaults, $options);
        $data = json_decode($response, true);
        $responseKey = $method === 'POST' ? 'form' : 'args';

        $this->assertEquals($expectedArgs, $data[$responseKey]);
    }

    public function scanDataProvider(): iterable
    {
        yield 'GET method' => ['GET'];
        yield 'POST method' => ['POST'];
        yield 'Override default version' => ['GET', ['version' => '2.0']];
        yield 'Override default maximumTerms' => ['GET', ['maximumRecords' => '42']];
        yield 'Call options' => ['GET', ['version' => '2.0'], ['version' => '3.0', 'responsePosition' => 5]];
    }

    private function baseUrl(string $endpoint): string
    {
        $host = getenv('HTTPBIN_HOST');
        if (!$host) {
            $host = 'https://httpbin.org';
        }
        return $host . '/' . strtolower($endpoint);
    }
}
