<?php

namespace YuanBen\YuanBenLian;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class BlockRequest
{
    const METADATA_URL = "/v1/metadata";
    const BLOCK_HASH_URL = "/v1/block_hash";
    private $client;


    public function __construct()
    {
        //TODO 根据环境修改请求地址 可设置成配置
        $url = "https://testnet.yuanbenlian.com";
        $this->client = new Client(
            ["base_uri" => $url]
        );
    }

    /**
     * @param $metadataJson
     * @return array|bool
     */
    public function _createMetadata($metadataJson)
    {
        try {
            $response = $this->client->post(self::METADATA_URL, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => $metadataJson
            ]);
        } catch (ClientException $e) {
            logger($e->getMessage());
            return false;
        }
        if ($response->getStatusCode() != 200) {
            logger($response->getBody()->getContents());
            return false;
        }
        $content = $response->getBody()->getContents();
        $data = json_decode($content, true);
        return $data;
    }

    /**
     * @param $dna
     * @return array|bool
     */
    public function searchMetaData($dna)
    {
        $response = $this->client->get(self::METADATA_URL . "/$dna");
        if ($response->getStatusCode() != 200) {
            logger($response->getBody()->getContents());
            return false;
        }
        $content = $response->getBody()->getContents();
        $data = json_decode($content, true);
        return $data;
    }

    /**
     * 获取block msg
     * @author mingwang
     * @date 2018.3.30
     * @return array|bool
     */
    public function getBlockMsg()
    {
        $response = $this->client->get(self::BLOCK_HASH_URL);
        if ($response->getStatusCode() != 200) {
            logger($response->getBody()->getContents());
            return false;
        }
        $content = $response->getBody()->getContents();
        $contentArr = json_decode($content, true);
        if (!isset($contentArr['data'])) {
            logger('#信息#: 获取block hash失败,' . $contentArr["msg"]);
            return false;
        }
        return $contentArr['data'];
    }

}