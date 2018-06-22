<?php

require_once __DIR__."/vendor/autoload.php";

$secp256k=app(\YuanBen\YuanBenLian\Secp256k1::class);

$priKey=$secp256k->generatePrivateKey();

$pubKey=$secp256k->generatePublicKey($priKey);

$content="This is a message!";

$contentHash=$secp256k->getContentHash($content);

$msg32 = $secp256k->_toBinary32($contentHash);

$rSign=$secp256k->getRecoverableSign($priKey,$msg32);

$sign=$secp256k->getSign($priKey,$secp256k->_toBinary32($contentHash));

var_dump($secp256k->verifyRecoverableSign($pubKey,$rSign,$msg32));

var_dump($secp256k->verifySign($pubKey,$sign,$secp256k->_toBinary32($contentHash)));

