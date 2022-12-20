<?php

namespace CorvusPay\Service;

class PisTransactionService extends AbstractService
{
    /**
     * Checking transaction status.
     *
     * @param array $params the parameters of the request.
     *
     * @return mixed|null Server output in xml format.
     *
     * @throws \InvalidArgumentException
     */
    public function status($params = null)
    {
        $sortedParams = [];
        $sortedParams['order_number'] = $params['order_number'];
        $sortedParams['store_id'] = $this->getClient()->getStoreId();
        $sortedParams['timestamp'] = date('YmdHis');
        $sortedParams['version'] = $this->getClient()->getApiVersion();
        $sortedParams["signature"] = $this->calculateSignature($sortedParams);
        $sortedParams['currency_code'] = CheckoutService::CURRENCY_CODES[$params['currency_code']];

        $this->validateStatus($sortedParams);

        return $this->request('check_pis_status', $sortedParams);
    }

    /**
     * Validate the parameters of the request.
     *
     * @param array $params the parameters of the request.
     *
     * @throws \InvalidArgumentException
     *
     */
    public function validateStatus($params = [])
    {
        //Mandatory fields.
        if ( ! isset($params["order_number"]) || $params["order_number"] === "") {
            throw new \InvalidArgumentException('The field order_number is mandatory.');
        }
        if ( ! isset($params["store_id"]) || $params["store_id"] === "") {
            throw new \InvalidArgumentException('The field store_id is mandatory.');
        }
        if ( ! isset($params["signature"]) || $params["signature"] === "") {
            throw new \InvalidArgumentException('The field signature is mandatory.');
        }
        if ( ! isset($params["currency_code"]) || $params["currency_code"] === "") {
            throw new \InvalidArgumentException('The field currency_code is mandatory.');
        }
        if ( ! isset($params["timestamp"]) || $params["timestamp"] === "") {
            throw new \InvalidArgumentException('The field timestamp is mandatory.');
        }
        if ( ! isset($params["version"]) || $params["version"] === "") {
            throw new \InvalidArgumentException('The field version is mandatory.');
        }

        //Length limit of fields.
        if (strlen($params["order_number"]) > 36) {
            throw new \InvalidArgumentException('The maximum length of order_number is 36.');
        }
        if (strlen($params["signature"]) > 64) {
            throw new \InvalidArgumentException('The maximum length of signature is 64.');
        }

        //Valid values for fields.
        if (preg_match("/^\d+$/", $params["store_id"]) === 0) {
            throw new \InvalidArgumentException('Invalid value for store_id.');
        }
        if ( ! in_array($params["currency_code"], CheckoutService::CURRENCY_CODES)) {
            throw new \InvalidArgumentException('Invalid value for currency_code.');
        }
    }
}