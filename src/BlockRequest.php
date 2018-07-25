<?php

namespace YuanBen;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;

class BlockRequest
{
    const METADATA_URL = "/v1/metadata";
    const BLOCK_HASH_URL = "/v1/block_hash";
    private $client;
    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct($base_uri)
    {
        //TODO 根据环境修改请求地址 可设置成配置
        $this->client = new Client(
            ["base_uri" => $base_uri]
        );
    }

    /**
     * @param $metadataJson
     * @throws ClientException
     * @return ResponseInterface
     */
    public function createMetadata($metadataJson)
    {
        $this->response = $this->client->post(self::METADATA_URL, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $metadataJson
        ]);
        return $this->response;
    }

    /**
     * @param $dna
     * @throws ClientException
     * @return ResponseInterface
     */
    public function searchMetaData($dna)
    {
        $this->response = $this->client->get(self::METADATA_URL . "/$dna");
        return $this->response;
    }

    /**
     * @throws ClientException
     * @return ResponseInterface
     */
    public function getBlockMsg()
    {
        $this->response = $this->client->get(self::BLOCK_HASH_URL);
        return $this->response;
    }

}