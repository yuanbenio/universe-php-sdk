<?php

/*
 * 原本链要求所有metadata的内容均为string类型
 * 当metadata中的字段数据为空时请在请求时过滤掉,原本链服务端会对空的字段值进行移除,会导致签名验证失败
 */

require_once __DIR__ . "/vendor/autoload.php";

$url = "https://testnet.yuanbenlian.com";

testImage($url);

function testArticle($url)
{
    $handle = new \YuanBen\BlockHandle();
    $privateKey = $handle->generatePrivateKey();
    $contentHash = $handle->getContentHash("测试文章");
    $request = new \YuanBen\BlockRequest(["base_uri" => $url]);
    $data = $request->getBlockMsg()->getBody()->getContents();
    $block = json_decode($data,true);
    $blockHash = $block['data']['latest_block_hash'];
    $blockHeight = (string)$block['data']['latest_block_height'];
    $metadataArr = [
        'language' => 'zh-CN',
        'type' => 'article',
        'title' => 'test',
        'category' => 'test',
        // 特殊字段
        'created' => (string)time(),
        'content_hash' => $contentHash,
        'license' => [
            'type' => 'cc',
            'parameters' => [
                "adaptation" => "y",
                "commercial" => "y"
            ],
        ],
        'block_hash' => $blockHash,
        'block_height' => (string)$blockHeight,
    ];
    $metadataArr = $handle->supplyMetadataArr($privateKey, $metadataArr);
    $sign = $handle->getSign($privateKey, $metadataArr);
    $metadataArr['signature'] = $sign;
    $dna = $handle->generateDNA($sign);
    $metadataArr['dna'] = $dna;
    $metadataJson = json_encode($metadataArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    // TODO 可自定义方法对必须字段进行校验
    try {
        $createMetadataRes = $request->createMetadata($metadataJson)->getBody()->getContents();
        logger($createMetadataRes);
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        logger($e->getMessage());
    }
}

function testImage($url)
{
    $handle = new \YuanBen\BlockHandle();
    $privateKey = $handle->generatePrivateKey();
    $contentHash = $handle->getContentHash(file_get_contents(__DIR__ . "/images/user1.png"));
    $request = new \YuanBen\BlockRequest(["base_uri" => $url]);
    $data = $request->getBlockMsg()->getBody()->getContents();
    $block = json_decode($data,true);
    $blockHash = $block['data']['latest_block_hash'];
    $blockHeight = (string)$block['data']['latest_block_height'];
    $metadataArr = [
        'language' => 'zh-CN',
        'type' => 'image',
        'title' => 'user',
        'category' => 'test',
        // 特殊字段
        'created' => (string)time(),
        'content_hash' => $contentHash,
        // 类型数据(可没有)  具体数据参考 http://yuanbenlian.mydoc.io/docs/api.md?t=268053
        /* 'data'=>[
            "thumb"=>"",
         ],*/
        'license' => [
            'type' => 'cc',
            'parameters' => [
                "adaptation" => "y",
                "commercial" => "y"
            ],
        ],
        'block_hash' => $blockHash,
        'block_height' => (string)$blockHeight,
    ];
    $metadataArr = $handle->supplyMetadataArr($privateKey, $metadataArr);
    $sign = $handle->getSign($privateKey, $metadataArr);
    $metadataArr['signature'] = $sign;
    $dna = $handle->generateDNA($sign);
    $metadataArr['dna'] = $dna;
    $metadataJson = json_encode($metadataArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    // TODO 可自定义方法对必须字段进行校验
    try {
        $createMetadataRes = $request->createMetadata($metadataJson)->getBody()->getContents();
        logger($createMetadataRes);
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        logger($e->getMessage());
    }
}





