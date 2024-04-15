<?php

namespace CorvusPay;

use Psr\Log\LoggerInterface;

/**
 * Class SSlVersion used as enum. Enums were introduced in PHP 8.1 but we use this approach to support clients running older versions of PHP.
 */
class SSlVersion {
    const OPENSSL = 1;
    const NSS = 2;
    const GNUTLS = 3;
}

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
        'test' => 'https://testcps.corvus.hr/'
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
     * @var bool|string P12 or PEM Client Certificate.
     */
    private $certificate;

    /**
     * The private key associated with the certificate.
     *
     * @var bool|string The private key associated with the certificate.
     */
    private $certificate_key;

    /**
     * Environment.
     *
     * @var string Environment.
     */
    private $environment;

    /**
     * SSL version.
     *
     * @var SSlVersion SSL version.
     */
    private $ssl_version;

    /**
     * ApiRequestor constructor.
     *
     * @param string $certificate
     * @param string $certificate_key
     * @param string $environment
     * @param LoggerInterface $logger
     *
     */
	public function __construct( $certificate = null, $certificate_key = null, $environment = null, $logger = null ) {
		$this->logger      = $logger;
		$this->environment = $environment;
		$curl_info         = curl_version();

		if ( strpos( $curl_info['ssl_version'], 'OpenSSL/' ) !== false ) {
			$this->ssl_version = SSlVersion::OPENSSL;
			$this->certificate = tempnam( sys_get_temp_dir(), '_' );
			if ( false === $this->certificate ) {
				$this->logger->error( 'Failed to create temporary file.' );

				return;
			}

			if ( false === file_put_contents( $this->certificate, base64_decode( $certificate ) ) ) {
				$this->logger->error( 'Failed to write to temporary file.' );
			}

			$curl_options = array(
				CURLOPT_POST           => 1,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSLCERT        => $this->certificate,
				CURLOPT_SSLCERTTYPE    => 'P12',
				CURLOPT_SSL_VERIFYPEER => true
			);

		} else if ( strpos( $curl_info['ssl_version'], 'NSS/' ) !== false || strpos( $curl_info['ssl_version'], 'GnuTLS/' ) !== false ) {
            $this->ssl_version     = strpos( $curl_info['ssl_version'], 'NSS/' ) !== false ? SSlVersion::NSS : SSlVersion::GNUTLS;
			$this->certificate     = tempnam( sys_get_temp_dir(), '_crt' );
			$this->certificate_key = tempnam( sys_get_temp_dir(), '_key' );

			if ( false === $this->certificate_key || false === $this->certificate ) {
				$this->logger->error( 'Failed to create temporary files.' );

				return;
			}

			if ( false === file_put_contents( $this->certificate, base64_decode( $certificate ) ) || false === file_put_contents( $this->certificate_key, base64_decode( $certificate_key ) ) ) {
				$this->logger->error( 'Failed to write to temporary files.' );

				return;
			}

			$curl_options = array(
				CURLOPT_POST           => 1,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_SSLCERT        => $this->certificate,
				CURLOPT_SSLKEY         => $this->certificate_key,
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_SSLCERTTYPE    => "PEM",
				CURLOPT_SSLKEYTYPE     => "PEM"
			);
		} else {
			$this->logger->error( 'Incompatible ssl version of curl.' );

			return;
		}

		$this->ch = curl_init();


		if ( false === $this->ch ) {
			$this->logger->error( 'Failed to initialize curl. Error: ' . curl_error( $this->ch ) );

			return;
		}

		if ( ! curl_setopt_array( $this->ch, $curl_options ) ) {
			$this->logger->error( 'Failed to set curl options. Error: ' . curl_error( $this->ch ) );
			$this->logger->debug( '$curl_options: ' . json_encode( $curl_options ) );
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
    public function __destruct() {
	    curl_close( $this->ch );
	    if ( $this->ssl_version === SSlVersion::OPENSSL ) {
		    if ( false === unlink( $this->certificate ) ) {
			    $this->logger->error( 'Failed to unlink file : ' . $this->certificate );

			    return;
		    }
		    $this->logger->debug( 'Destructing ApiRequestor with certificate in file "' . $this->certificate . '".' );
	    } else if ( $this->ssl_version === SSlVersion::NSS || $this->ssl_version === SSlVersion::GNUTLS ) {
		    if ( false === unlink( $this->certificate ) ) {
			    $this->logger->error( 'Failed to unlink file: ' . $this->certificate );

			    return;
		    }
		    if ( false === unlink( $this->certificate_key ) ) {
			    $this->logger->error( 'Failed to unlink file: ' . $this->certificate_key );

			    return;
		    }
		    $this->logger->debug( 'Destructing ApiRequestor with certificate in files "' . $this->certificate . '" and "' . $this->certificate_key . '".' );
	    }
    }
}