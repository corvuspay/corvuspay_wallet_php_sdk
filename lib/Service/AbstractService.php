<?php

namespace CorvusPay\Service;

use CorvusPay\CorvusPayClientInterface;

/**
 * Abstract base class for all services.
 */
abstract class AbstractService
{
    /**
     * @var CorvusPayClientInterface
     */
    protected $client;

    /**
     * Initializes a new instance of the {@link AbstractService} class.
     *
     * @param CorvusPayClientInterface $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Gets the client used by this service to send requests.
     *
     * @return CorvusPayClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Calculate 'signature' parameter. Signs order using Secret Key. Depends on environment.
     *
     * @param array $params parameters.
     *
     * @return string returns signature.
     */
    public function calculateSignature($params)
    {
    	$sortedParameters = $params;
        ksort($sortedParameters);
        $data = '';
        foreach ($sortedParameters as $key => $value) {
            $data .= $key . $value;
        }

        return hash_hmac(
            'sha256',
            $data,
            $this->client->getSecretKey()
        );
    }

    /**
     * Calculate 'hash' parameter.
     *
     * @param array $params array with params.
     *
     * @return string returns hash.
     */
    public function calculateHash(array $params)
    {
        $params['hash']     = sha1(
            $this->getClient()->getSecretKey() .
            implode($params)
        );

        return $params['hash'];
    }

    /**
     * Sends a request to CorvusPay's API.
     *
     * @param string $endpoint API endpoint.
     * @param array  $params POST parameters.
     *
     * @return mixed|null Server output.
     */
    protected function request($endpoint, $params)
    {
        return $this->getClient()->request($endpoint, $params);
    }

    /**
     * Validate the parameters of the request.
     *
     * @param array $params the parameters of the request.
     *
     * @throws \InvalidArgumentException
     *
     */
    public function validate($params = [])
    {
        //Mandatory fields.
        if ( ! isset($params["order_number"]) || $params["order_number"] === "") {
            throw new \InvalidArgumentException('The field order_number is mandatory.');
        }
        if ( ! isset($params["store_id"]) || $params["store_id"] === "") {
            throw new \InvalidArgumentException('The field store_id is mandatory.');
        }
        if ( ! isset($params["hash"]) || $params["hash"] === "") {
            throw new \InvalidArgumentException('The field hash is mandatory.');
        }

        //Length limit of fields.
        if (strlen($params["order_number"]) > 36) {
            throw new \InvalidArgumentException('The maximum length of order_number is 36.');
        }
        if (strlen($params["hash"]) > 40) {
            throw new \InvalidArgumentException('The maximum length of hash is 40.');
        }

        //Valid values for fields.
        if (preg_match("/^\d+$/", $params["store_id"]) === 0) {
            throw new \InvalidArgumentException('Invalid value for store_id.');
        }
    }


}