<?php

include __DIR__ . "/../vendor/autoload.php";

// generate private key
$privateKey = \YBL\Kernel\Eth\Key::createPrivateKey();
echo $privateKey . PHP_EOL;

// init key
\YBL\Kernel\Eth\Key::init($privateKey);
$private_key = \YBL\Kernel\Eth\Key::getPrivateKey()->getHex();

// if you use production env,You can contact the YuanBenLian owner to add the public key to the authorized public key.
$public_key = \YBL\Kernel\Eth\Key::getPublicKey()->getHex();