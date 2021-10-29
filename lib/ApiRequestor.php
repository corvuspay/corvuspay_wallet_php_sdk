<?php

namespace CorvusPay;

use Psr\Log\LoggerInterface;

/**
 * Class ApiRequestor.
 */
class ApiRequestor
{
    /**
     * CorvusPay API endpoints for test and production.
     */
    const API_ENDPOINTS = array(
        'prod' => 'https://cps.corvus.hr/',
        'test' => 'https://testcps.corvus.hr/',
    );

    /**
     * The logger to be used for all messages
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * A cURL handle.
     *
     * @var resource A cURL handle.
     */
    private $ch;

    /**
     * Client Certificate for CorvusPay API.
     *
     * @var bool|string Client Certificate for CorvusPay API.
     */
    private $certificate;
    /**
     * Environment.
     *
     * @var string Environment.
     */
    private $environment;

    /**
     * ApiRequestor constructor.
     *
     * @param string $certificate
     * @param string $environment
     * @param LoggerInterface $logger
     *
     */
    public function __construct($certificate = null, $environment = null, $logger = null)
    {
        $this->logger      = $logger;
        $this->environment = $environment;
        $curl_info = curl_version();

        // if ssl_version is NSS log error.
        if (!(strpos(strtolower($curl_info['ssl_version']), 'openssl') !== false)) {
            $this->logger->error('Incompatible ssl version of curl.');

            return;
        }
        // Create certificate file for cURL.
        $this->certificate = tempnam(sys_get_temp_dir(), '_');
        if (false === $this->certificate) {
            $this->logger->error('Failed to create temporary file.');

            return;
        }

        if (false === file_put_contents($this->certificate, base64_decode($certificate))) {
            $this->logger->error('Failed to write to temporary file.');
        }

        $this->ch = curl_init();

        if (false === $this->ch) {
            $this->logger->error('Failed to initialize curl. Error: ' . curl_error($this->ch));

            return;
        }

        $curl_options = array(
            CURLOPT_POST           => 1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSLCERT        => $this->certificate,
            CURLOPT_SSLCERTTYPE    => 'P12',
            CURLOPT_SSL_VERIFYPEER => true
        );


        if ( ! curl_setopt_array($this->ch, $curl_options)) {
            $this->logger->error('Failed to set curl options. Error: ' . curl_error($this->ch));
            $this->logger->debug('$curl_options: ' . json_encode($curl_options));

            return;
        }
    }

    /**
     * Run a query on CorvusPay API.
     *
     * @param string $endpoint API endpoint.
     * @param array $parameters POST parameters.
     *
     * @return mixed|null Server output.
     */
    public function request($endpoint, $parameters)
    {
        $this->logger->debug('Running API query. $endpoint: ' . $endpoint . ' $parameters: ' . json_encode($parameters));

        $curl_options = array(
            CURLOPT_URL        => self::API_ENDPOINTS[ $this->environment ] . $endpoint,
            CURLOPT_POSTFIELDS => http_build_query($parameters),
        );

        if ( ! curl_setopt_array($this->ch, $curl_options)) {
            $this->logger->error('Failed to set curl options. Error: ' . curl_error($this->ch));
            $this->logger->debug('$curl_options: ' . json_encode($curl_options));

            return false;
        }

        $result = curl_exec($this->ch);

        if (false === $result) {
            $this->logger->error('Failed to execute curl query. Error: ' . curl_error($this->ch));

            return false;
        }

        return $result;
    }

    /**
     * ApiRequestor destructor.
     */
    public function __destruct()
    {
        curl_close($this->ch);
        if ( false === unlink( $this->certificate ) ) {
            $this->logger->error( 'Failed to unlink file.' );

            return;
        }
        $this->logger->debug( 'Destructing ApiRequestor with certificate in file "' . $this->certificate . '".' );
    }
}