<?php

namespace CorvusPay\Service;

class CoreServiceFactory extends AbstractServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static $classMap = [
        'checkout' => CheckoutService::class,
        'transaction' => TransactionService::class,
        'pisTransaction' => PisTransactionService::class,
        'subscription' => SubscriptionService::class,
        'validate' => ValidationService::class
    ];

    protected function getServiceClass($name)
    {
        return array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}