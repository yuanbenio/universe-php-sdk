<?php

/*
 * 原本链要求所有metadata的内容均为string类型
 * 当metadata中的字段数据为空时请在请求时过滤掉,原本链服务端会对空的字段值进行移除,会导致签名验证失败
 */




require_once __DIR__ . "/vendor/autoload.php";

testImage();

function testArticle()
{
    $handle = app(\YuanBen\YuanBenLian\BlockHandle::class);
    $privateKey = $handle->generatePrivateKey();
    $contentHash = $handle->getContentHash("测试文章");
    $request = app(\YuanBen\YuanBenLian\BlockRequest::class);
    $data = $request->getBlockMsg();
    if (!$data) {
        // TODO
        logger("获取最新区块高度失败");
    }
    $blockHash = $data['latest_block_hash'];
    $blockHeight = (string)$data['latest_block_height'];
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
    // TODO 可自定义方法对必须字段进行校验
    $createMetadataRes = $request->_createMetadata(json_encode($metadataArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    logger($createMetadataRes);
}

function testImage()
{
    $handle = app(\YuanBen\YuanBenLian\BlockHandle::class);
    $privateKey = $handle->generatePrivateKey();
    $contentHash = $handle->getContentHash(file_get_contents(__DIR__ . "/images/user1.png"));
    $request = app(\YuanBen\YuanBenLian\BlockRequest::class);
    $data = $request->getBlockMsg();
    if (!$data) {
        // TODO
        logger("获取最新区块高度失败");
    }
    $blockHash = $data['latest_block_hash'];
    $blockHeight = (string)$data['latest_block_height'];
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
    // TODO 可自定义方法对必须字段进行校验
    $createMetadataRes = $request->_createMetadata(json_encode($metadataArr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    logger($createMetadataRes);
}





