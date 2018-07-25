# 原本链SDK for PHP

### 功能
* 使用php接入原本链
> 原本链API文档 [http://yuanbenlian.mydoc.io/docs/api.md?t=268053](http://yuanbenlian.mydoc.io/docs/api.md?t=268053)

### 安装

> 请在Linux系统下运行

> PHP Version >= 7.0

* ext-secp256k1: [https://github.com/Bit-Wasp/secp256k1-php](https://github.com/Bit-Wasp/secp256k1-php)
* ext-keccak: [https://github.com/archwisp/php-keccak-hash](https://github.com/archwisp/php-keccak-hash)

* git 安装
> git clone https://github.com/yuanbenio/universe-php-sdk.git & composer install

* composer 安装
> composer require xiongchao/universe-php-sdk
   
### SDK使用

#### 1.请求上链

* 以接入图片类型示例

```php
require_once __DIR__ . "/vendor/autoload.php";
$src = __DIR__ . "/images/user1.png";
$url = "https://testnet.yuanbenlian.com";  // 原本链测试线地址
$handle = app(\YuanBen\YuanBenLian\BlockHandle::class);
$request = app(\YuanBen\YuanBenLian\BlockRequest::class, ["base_uri" => $url]);
$privateKey = $handle->generatePrivateKey();
$contentHash = $handle->getContentHash(file_get_contents($src));
$data = $request->getBlockMsg()->getBody()->getContents();
$block = json_decode($data,true);
$blockHash = $block['data']['latest_block_hash'];
$blockHeight = (string)$block['data']['latest_block_height']; // 原本链要求所有metadata的内容均为string类型
// 拼接 metadata
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
// 补全metadata 公钥 & ID,也可自行拼接
$metadataArr = $handle->supplyMetadataArr($privateKey, $metadataArr);
// 对metadata进行签名时需先按照键值排序在进行签名,案例已封装于getSign方法中
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
```

#### 根据dna信息查询元数据

```php
$request = app(\YuanBen\YuanBenLian\BlockRequest::class,["base_uri"=>$url]);
$metadata=$request->searchMetaData($dna)->getBody()->getContents();
```