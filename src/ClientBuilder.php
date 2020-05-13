<?php

namespace YBL;

use GuzzleHttp\Client;
use YBL\Kernel\Eth\Key;
use YBL\Kernel\Exceptions\ParameterException;
use YBL\Kernel\Traits\MetadataTrait;

class ClientBuilder
{
    use MetadataTrait;
    /**
     * Server version
     */
    const SERVER_VERSION = "v1";

    /**
     * ybl api request options
     * timeout : Float describing the timeout of the request in seconds. default 30 s.
     * verify : Describes the SSL certificate verification behavior of a request.
     * Set to true to enable SSL certificate verification and use the default CA bundle provided by operating system.
     * Set to false to disable certificate verification (this is insecure!).
     * Set to a string to provide the path to a CA bundle to enable verification using a custom certificate.
     *
     * @var array
     */
    private $httpOptions = array(
        'base_uri' => 'https://openapi.yuanbenlian.com',
        'timeout' => 30,
        'verify' => true,
        'headers' => [
            'Content-Type' => 'application/json',
        ]
    );

    private $client;

    /**
     * ClientBuilder constructor.
     * @param array $config
     * @throws ParameterException
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['private_key'])) {
            throw new ParameterException("private_key is must need!");
        }

        Key::init($config['private_key']);

        $this->privateKey = Key::getPrivateKey();

        $this->publicKey = Key::getPublicKey();

        if (isset($config['base_uri'])) {
            if (substr($config['base_uri'], -1) === '/') {
                $config['base_uri'] = substr($config['base_uri'], -1, strlen($config['base_uri']) - 1);
            }
            $this->httpOptions['base_uri'] = $config['base_uri'] . '/' . self::SERVER_VERSION . "/";
        } else {
            $this->httpOptions['base_uri'] .= '/' . self::SERVER_VERSION . "/";
        }

        if (isset($config['timeout'])) {
            $this->httpOptions['timeout'] = $config['timeout'];
        }

        if (isset($config['verify'])) {
            $this->httpOptions['verify'] = $config['verify'];
        }

        $this->client = new Client($this->httpOptions);
    }

    /**
     * @param array $metadata
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function publishMetadata(array $metadata)
    {
        return $this->client->post('metadata', [
            'body' => json_encode($metadata),
        ]);
    }

    public function searchMetadata(string $dna)
    {
        return $this->client->get('metadata/' . $dna);
    }

    public function searchLatestBlock()
    {
        return $this->client->get('block_hash');
    }

    public function registerAccount(array $subkeys)
    {
        $signature = $this->sign($subkeys);
        $metadata = [
            "subkeys" => $subkeys,
            "pubkey" => $this->publicKey->getHex(),
        ];
        $metadata = $this->setSignature($metadata, $signature);

        return $this->client->post('accounts', [
            'body' => json_encode($metadata),
        ]);
    }
}