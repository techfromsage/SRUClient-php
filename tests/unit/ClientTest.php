<?php

namespace SRU\tests;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use PHPUnit\Framework\TestCase;
use SRU\Client;

class ClientTest extends TestCase
{
    /** @var string */
    static protected $httpbinHost;

    public static function setUpBeforeClass(): void
    {
        self::$httpbinHost = getenv('HTTPBIN_HOST') ?: 'https://httpbin.org';
    }

    /**
     * @dataProvider explainDataProvider
     */
    public function testExplain(string $method, array $defaults = [])
    {
        $clientOptions = $defaults + ['httpMethod' => $method];
        $client = new Client(self::$httpbinHost . '/anything', $clientOptions);

        $response = $client->explain(true);

        $expectedArgs = array_merge([
            'operation' => 'explain',
            'version' => '1.1',
        ], $defaults);
        $data = json_decode($response, true);
        $responseKey = $method === 'POST' ? 'form' : 'args';

        $this->assertEquals($method, $data['method']);
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
        $client = new Client(self::$httpbinHost . '/anything', $clientOptions);

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

        $this->assertEquals($method, $data['method']);
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
        $client = new Client(self::$httpbinHost . '/anything', $clientOptions);

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

        $this->assertEquals($method, $data['method']);
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

    /**
     * @dataProvider clientErrorStatusProvider
     */
    public function testThrowsClientException(int $status)
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode($status);

        $client = new Client(self::$httpbinHost . '/status/' . $status);
        $client->explain(true);
    }

    public function clientErrorStatusProvider(): iterable
    {
        yield 'Bad Request' => [400];
        yield 'Unauthorized' => [401];
        yield 'Forbidden' => [403];
        yield 'Not Found' => [404];
    }

    /**
     * @dataProvider serverErrorStatusProvider
     */
    public function testThrowsServerException(int $status)
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionCode($status);

        $client = new Client(self::$httpbinHost . '/status/' . $status);
        $client->explain(true);
    }

    public function serverErrorStatusProvider(): iterable
    {
        yield 'Internal Server Error' => [500];
        yield 'Bad Gateway' => [502];
        yield 'Service Unavailable' => [503];
        yield 'Gateway Timeout' => [504];
    }
}
