<?php

namespace SRU;

use GuzzleHttp\RequestOptions;

class Client
{
    /**
     * Base URL of SRU service
     * @var string
     */
    private $baseUrl;
    
    /**
     * Default record schema for requests
     * @var string
     */
    private $defaultRecordSchema;
    
    /**
     * Default max number of records to return
     * @var int
     */
    private $defaultMaximumRecords = 10;
    
    /**
     * Default HTTP method to use for requests
     * @var string
     */
    private $defaultHttpMethod;

    /**
     * Default SRU standard version to request
     * @var string
     */
    private $defaultSRUVersion = "1.1";

    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * @param string $baseUrl The base URL of the SRU service
     * @param array $options An array of options for the SRU service
     *                       ('recordSchema', 'maximumRecords', 'httpMethod', 'version')
     */
    public function __construct($baseUrl, $options = [])
    {
        $this->baseUrl = $baseUrl;
        if (isset($options['recordSchema'])) {
            $this->defaultRecordSchema = $options['recordSchema'];
        }
        if (isset($options['maximumRecords'])) {
            $this->defaultMaximumRecords = $options['maximumRecords'];
        }
        if (isset($options['httpMethod'])) {
            $this->defaultHttpMethod = $options['httpMethod'];
        }
        if (isset($options['version'])) {
            $this->defaultSRUVersion = $options['version'];
        }
    }

    /**
     * Returns a DOMDocument of the explain operation response (or a string if $raw is true)
     * @param bool $raw
     * @return \DOMDocument|string
     */
    public function explain($raw = false)
    {
        $explainResponse = $this->fetch(
            ['version' => $this->getDefaultSRUVersion(), 'operation' => 'explain']
        );
        $body = (string) $explainResponse->getBody();
        if ($raw) {
            return $body;
        }

        $explain = new \DOMDocument();
        $explain->loadXML($body);

        return $explain;
    }

    /**
     * Alias for searchRetrieve()
     *
     * @param string $query
     * @param array $options
     * @param bool $raw
     * @return SearchRetrieveResponse|string
     */
    public function search($query, $options = [], $raw = false)
    {
        return $this->searchRetrieve($query, $options, $raw);
    }

    /**
     * Performs a searchRetrieve operation and returns a SearchRetrieveResponse object or a string is $raw is true
     *
     * @param string $query The CQL query string
     * @param array $options The query options
     *                       ('version', 'maximumRecords', 'startRecord', 'recordSchema', 'recordPacking')
     * @param bool $raw If true, returns the response as a string
     * @return SearchRetrieveResponse|string
     */
    public function searchRetrieve($query, $options = [], $raw = false)
    {
        $defaultOptions = [
            'version'        => $this->getDefaultSRUVersion(),
            'maximumRecords' => $this->getDefaultMaximumRecords(),
            'startRecord'    => 1,
            'recordPacking'  => 'xml'
        ];
        if ($this->defaultRecordSchema) {
            $defaultOptions['recordSchema'] = $this->defaultRecordSchema;
        }

        $searchRetrieveResponse = $this->fetch(
            array_merge($defaultOptions, $options, ['operation' => 'searchRetrieve', 'query' => $query])
        );
        $body = (string) $searchRetrieveResponse->getBody();
        if ($raw) {
            return $body;
        }

        $searchXML = new \DOMDocument();
        $searchXML->loadXML($body);

        $searchRetrieve = new SearchRetrieveResponse($searchXML);

        return $searchRetrieve;
    }

    /**
     * Performs a scan operation and returns a ScanResponse object or a string is $raw is true
     *
     * @param string $query The CQL query string
     * @param array $options The query options ('version', 'maximumTerms', 'scanClause')
     * @param bool $raw If true, returns the response as a string
     * @return SearchRetrieveResponse|string
     */
    public function scan($scanClause, $options = [], $raw = false)
    {
        $defaultOptions = [
            'operation' => 'scan',
            'version'   => $this->getDefaultSRUVersion(),
            'maximumTerms' => $this->getDefaultMaximumRecords()
        ];

        $scanResponse = $this->fetch(
            array_merge($defaultOptions, $options, ['operation' => 'scan', 'scanClause' => $scanClause])
        );
        $body = (string) $scanResponse->getBody();
        if ($raw) {
            return $body;
        }

        $scan = new ScanResponse($body);
        return $scan;
    }

    /**
     * Returns the supported recordSchema identifiers as defined in the explain response
     * @return array
     */
    public function recordSchemas()
    {
        $explain = $this->explain();
        $xpath = new \DOMXPath($explain);
        $xpath->registerNamespace('zs', 'http://www.loc.gov/zing/srw/');
        $xpath->registerNamespace('ex', 'http://explain.z3950.org/dtd/2.0/');

        $nodes = $xpath->query(
            '/zs:explainResponse/zs:record/zs:recordData/ex:explain/ex:schemaInfo/ex:schema'
        );
        $schemas = [];
        foreach ($nodes as $node) {
            $schemas[$node->getAttribute("name")] = ['identifier' => $node->getAttribute('identifier')];
            $titleList = $node->getElementsByTagName('title');
            foreach ($titleList as $title) {
                $schemas[$node->getAttribute('name')]['title'] = $title->nodeValue;
            }
        }
        return $schemas;
    }

    /**
     * Returns the supported indexes as defined in the explain response
     * @return array
     */
    public function indexes()
    {
        $explain = $this->explain();
        $xpath = new \DOMXPath($explain);
        $xpath->registerNamespace('zs', 'http://www.loc.gov/zing/srw/');
        $xpath->registerNamespace('ex', 'http://explain.z3950.org/dtd/2.0/');

        $nodes = $xpath->query(
            '/zs:explainResponse/zs:record/zs:recordData/ex:explain/ex:indexInfo/ex:index/ex:map/ex:name'
        );
        $indexes = [];
        foreach ($nodes as $node) {
            $idx = ['set' => $node->getAttribute('set'), 'name' => $node->nodeValue];
            $nodeList = $node->parentNode->parentNode->getElementsByTagName('title');
            foreach ($nodeList as $title) {
                $idx['title'] = $title->nodeValue;
            }
            $indexes[$node->getAttribute('set') . '.' . $node->nodeValue] = $idx;
        }
        return $indexes;
    }

    /**
     * Performs the HTTP request based on the defined request method
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function fetch(array $args = [])
    {
        $client = $this->getHttpClient();

        if ($this->defaultHttpMethod === 'GET') {
            if (empty($args)) {
                $url = $this->getBaseUrl();
            } else {
                $url = $this->getBaseUrl() . '?' . http_build_query($args);
            }

            return $client->get($url);
        }

        return $client->post($this->getBaseUrl(), [
            RequestOptions::FORM_PARAMS => $args,
        ]);
    }

    /**
     * Returns the base URL of the SRU service
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Sets the base URL of the SRU service
     * @param string $baseUrl
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * Returns the default record schema
     *
     * @return string
     */
    public function getDefaultRecordSchema()
    {
        return $this->defaultRecordSchema;
    }

    /**
     * Sets the default record schema for searchRetrieve requests
     *
     * @param string $defaultRecordSchema
     */
    public function setDefaultRecordSchema($defaultRecordSchema)
    {
        $this->defaultRecordSchema = $defaultRecordSchema;
    }

    /**
     * Returns the default maximum number of records to return in a searchRetrieve request
     * @return int
     */
    public function getDefaultMaximumRecords()
    {
        return $this->defaultMaximumRecords;
    }

    /**
     * Sets the default maximum number of records to return in a searchRetrieve request
     * @param int $defaultMaximumRecords
     */
    public function setDefaultMaximumRecords($defaultMaximumRecords)
    {
        $this->defaultMaximumRecords = $defaultMaximumRecords;
    }

    /**
     * Returns the HTTP method client will use with SRU service by default
     *
     * @return string
     */
    public function getDefaultHttpMethod()
    {
        return $this->defaultHttpMethod;
    }

    /**
     * Sets the HTTP method the client will use with the SRU service by default
     * @param string $defaultHttpMethod
     */
    public function setDefaultHttpMethod($defaultHttpMethod)
    {
        $this->defaultHttpMethod = $defaultHttpMethod;
    }

    /**
     * Lazy loader for the GuzzleClient
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if (!$this->httpClient) {
            $this->httpClient = new \GuzzleHttp\Client();
        }
        return $this->httpClient;
    }

    /**
     * @return string
     */
    public function getDefaultSRUVersion()
    {
        return $this->defaultSRUVersion;
    }

    /**
     * @param string $defaultSRUVersion
     */
    public function setDefaultSRUVersion($defaultSRUVersion)
    {
        $this->defaultSRUVersion = $defaultSRUVersion;
    }
}
