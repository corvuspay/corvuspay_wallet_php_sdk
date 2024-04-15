<?php

namespace CorvusPay;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class BaseCorvusPayClient implements CorvusPayClientInterface
{
    use LoggerAwareTrait;

    /** @var string CorvusPay API version. */
    const API_VERSION = '1.4';

    /** @var string CorvusPay production environment. */
    const PRODUCTION = 'prod';

    /** @var string CorvusPay sandbox environment. */
    const SANDBOX = 'test';

    /** @var array<string, mixed> */
    private $config;

    /**
     * Initializes a new instance of the {@link BaseCorvusPayClient} class.
     *
     * The constructor takes a single argument.
     * The argument can be an array with the following options:
     *
     * - secret_key (null|string): the CorvusPay secret key, to be used in requests.
     * - store_id(null|int): the CorvusPay store ID, to be used in requests.
     * - environment (null|string): Environment, either 'test' or 'prod'. Default environment is test environment.
     * - version (null|string): a CorvusPay API version.
     * - test_certificate (null|resource): the certificate from CorvusPay , to be used in requests for the
     * test environment, encoded with MIME base64.
     * - prod_certificate (null|resource): the certificate from CorvusPay , to be used in requests for the
     * production environment, encoded with MIME base64.
     * - logger (null|LoggerInterface): the logger to be used for all messages.
     *
     *
     * @param array<string, mixed> $config An array containing the client configuration settings.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($config = [])
    {
        if ( ! is_array($config)) {
            throw new \InvalidArgumentException('$config must be an array');
        }

        $config       = array_merge($this->getDefaultConfig(), $config);
        $this->logger = $config['logger'];
        $this->config = $config;
    }

    /**
     * Returns the default values for configuration.
     *
     * @return array<string, mixed>
     */
    private function getDefaultConfig()
    {
        return [
            'secret_key'       => null,
            'store_id'         => null,
            'version'          => self::API_VERSION,
            'environment'      => self::SANDBOX,
            'test_certificate' => null,
            'prod_certificate' => null,
            'logger'           => new NullLogger()
        ];
    }

    /**
     * Gets the logger which will be used for all messages.
     *
     * @return null|LoggerInterface the logger to be used for all messages.
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Gets the Secret key used by the client to send requests.
     *
     * @return null|string the Secret key used by the client to send requests
     */
    public function getSecretKey()
    {
        return $this->config['secret_key'];
    }

    /**
     * Gets the Store Id used by the client to send requests.
     *
     * @return null|int the Store Id used by the client to send requests
     */
    public function getStoreId()
    {
        return $this->config['store_id'];
    }

    /**
     * Gets the environment.
     *
     * @return null|string the environment value.
     */
    public function getEnvironment()
    {
        return $this->config['environment'];
    }

    /**
     * Gets the API version.
     *
     * @return null|string the version value.
     */
    public function getApiVersion()
    {
        return $this->config['version'];
    }

    /**
     * Sets the Secret key used by the client to send requests.
     *
     * @param string $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->config['secret_key'] = $secretKey;
    }

    /**
     * Sets the Store Id used by the client to send requests.
     *
     * @param int $storeId
     */
    public function setStoreId($storeId)
    {
        $this->config['store_id'] = $storeId;
    }

    /**
     * Sets the environment.
     *
     * @param string $environment
     */
    public function setEnvironment($environment)
    {
        $this->config['environment'] = $environment;
    }

    /**
     * Sets the API version.
     *
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->config['version'] = $version;
    }

    /**
     * Sets the certificate.
     *
     * @param resource|string $fileCertificate the certificate represented as file stream(resource) or as base64 string.
     * @param string $passwordCertificate the password of the certificate,
     * @param string $environment the environment in which the certificate will be valid,
     * if this additional parameter is not specified then the environment previously defined by the user
     * will be taken as the environment, and if it is not defined a test environment is set.
     *
     * @throws \InvalidArgumentException
     */
    public function setCertificate($fileCertificate, $passwordCertificate = null, $environment = null)
    {
        if ($environment == null) {
            $environment = $this->config['environment'];
        }
        if(gettype($fileCertificate) === "resource"){
            // If a certificate has been sent, read the contents and save Base64 encoded data instead.
            $pem_array = array();
            $content   = '';

            while ( ! feof($fileCertificate)) {
                $content .= fread($fileCertificate, 8192);
            }

            $read = openssl_pkcs12_read($content, $pem_array, $passwordCertificate);
            if ($read) {
                $this->logger->debug('openssl_pkcs12_read: ' . json_encode($pem_array));
            } else {
                $this->logger->error('Parsing the PKCS#12 Certificate Store into an array failed.');
                throw new \InvalidArgumentException('Certificate password is incorrect.');
            }

            $pkcs12_string = '';
            if (openssl_pkcs12_export($pem_array['cert'], $pkcs12_string, $pem_array['pkey'], '', $pem_array)) {
                $this->logger->debug('Exporting the PKCS#12 Compatible Certificate Store File to a variable succeeded.');
                $this->config[ $environment . '_certificate' ] = base64_encode($pkcs12_string);
            } else {
                $this->logger->error('Unable to store certificate');
            }
        }
        elseif (gettype($fileCertificate) === "string")
            $this->config[ $environment . '_certificate' ] = $fileCertificate;
        else
            throw new \InvalidArgumentException('$fileCertificate type not supported.');
    }

	/**
	 * Sets the certificate.
	 *
	 * @param string $crt the certificate encoded in the PEM format represented as base64 string.
	 * @param string $key the private key encoded in the PEM format represented as base64 string.
	 * @param string $environment the environment in which the certificate will be valid,
	 * if this additional parameter is not specified then the environment previously defined by the user
	 * will be taken as the environment, and if it is not defined a test environment is set.
	 *
	 * @throws \InvalidArgumentException
	 */
    public function setCertificateCrtAndKey($crt, $key, $environment = null)
    {
        if ($environment == null) {
            $environment = $this->config['environment'];
        }
        if (gettype($crt) === "string" && gettype($key) === "string") {
            $this->config[$environment . '_certificate_crt'] = $crt;
            $this->config[$environment . '_certificate_key'] = $key;
        }
        else
            throw new \InvalidArgumentException('type not supported');
    }

    /**
     * Sends a request to CorvusPay's API.
     *
     * @param string $endpoint API endpoint.
     * @param array $parameters POST parameters.
     *
     * @return mixed|null Server output.
     */
    public function request($endpoint, $parameters)
    {
	    if (strpos(curl_version()['ssl_version'], 'OpenSSL/') !== false) {
		    $requestor = new ApiRequestor($this->config[ $this->getEnvironment() . '_certificate' ],null, $this->getEnvironment(), $this->logger);

		    return $requestor->request($endpoint, $parameters);
	    } else if (strpos(curl_version()['ssl_version'], 'NSS/') !== false || strpos(curl_version()['ssl_version'], 'GnuTLS') !== false) {
		    $requestor = new ApiRequestor($this->config[ $this->getEnvironment() . '_certificate_crt' ],$this->config[ $this->getEnvironment() . '_certificate_key' ],
			    $this->getEnvironment(), $this->logger);

		    return $requestor->request($endpoint, $parameters);
	    } else {
		    $this->logger->error('Incompatible ssl version of curl.');
		    throw new \InvalidArgumentException('SSL version not supported');
	    }
    }

}
