<?php

namespace CorvusPay;

/**
 * Interface for a CorvusPay client.
 */
interface CorvusPayClientInterface
{
    /**
     * Gets the Secret key used by the client to send requests.
     *
     * @return null|string the Secret key used by the client to send requests
     */
    public function getSecretKey();

    /**
     * Gets the Store Id used by the client to send requests.
     *
     * @return null|int the Store Id used by the client to send requests
     */
    public function getStoreId();

    /**
     * Gets the environment.
     *
     * @return null|string the environment value.
     */
    public function getEnvironment();

    /**
     * Gets the API version.
     *
     * @return null|string the version value.
     */
    public function getApiVersion();

    /**
     * Sets the Secret key used by the client to send requests.
     *
     * @param string $secretKey
     */
    public function setSecretKey($secretKey);

    /**
     * Sets the Store Id used by the client to send requests.
     *
     * @param int $storeId
     */
    public function setStoreId($storeId);

    /**
     * Sets the environment.
     *
     * @param string $environment
     */
    public function setEnvironment($environment);

    /**
     * Sets the API version.
     *
     * @param string $version
     */
    public function setVersion($version);

    /**
     * Sets the certificate.
     *
     * @param resource $fileCertificate the file stream of the certificate.
     * @param string $passwordCertificate the password of the certificate,
     * @param string $environment the environment in which the certificate will be valid,
     * if this additional parameter is not specified then the environment previously defined by the user
     * will be taken as the environment, and if it is not defined a test environment is set.
     */
    public function setCertificate($fileCertificate, $passwordCertificate, $environment = null);
}