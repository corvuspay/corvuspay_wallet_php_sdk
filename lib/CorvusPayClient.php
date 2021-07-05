<?php

namespace CorvusPay;

use CorvusPay\Service\CoreServiceFactory;

/**
 * Client used to checkout and send requests to CorvusPay's API.
 *
 */
class CorvusPayClient extends BaseCorvusPayClient
{
    /**
     * @var CoreServiceFactory
     */
    private $coreServiceFactory;

    public function __get($name)
    {
        if (null === $this->coreServiceFactory) {
            $this->coreServiceFactory = new CoreServiceFactory($this);
        }

        return $this->coreServiceFactory->__get($name);
    }
}