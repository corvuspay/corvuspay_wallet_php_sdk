<?php

namespace CorvusPay\Service;

use SimpleXMLElement;

class TransactionService extends AbstractService
{
    /**
     * Complete a transaction or complete a transaction for subscription.
     *
     * @param array $params the parameters of the request.
     *
     * @param bool $xml_response if true response will be in xml with all details for the transaction,
     * if false response will be boolean indicating whether the request was successful.
     *
     * @return boolean|mixed|null True or xml_response if the transaction is successfully completed
     * or server output in xml format or false if error occurred.
     *
     * @throws \InvalidArgumentException
     */
    public function complete($params = null, $xml_response = false)
    {
        $params['store_id'] = $this->getClient()->getStoreId();
        $params["hash"]     = $this->calculateHash([$params["order_number"], $params["store_id"]]);

        $this->validateComplete($params);
        $response_xml = $this->request('complete', $params);
        try {
            $response = new SimpleXMLElement($response_xml);
        } catch (\Exception $e) {
            $this->getClient()->getLogger()->error('String could not be parsed as XML');
        }

        if (isset($response) && $response->{'response-code'}[0] == "0" && $xml_response === false) {
            return true;
        } else if (isset($response) && $response->{'response-code'}[0] != "0" && $xml_response === false) {
            $this->getClient()->getLogger()->error($response_xml);

            return false;
        } else return $response_xml;
    }

    /**
     * Partially complete a transaction.
     *
     * @param array $params the parameters of the request.
     *
     * @param bool $xml_response if true response will be in xml with all details for the transaction,
     * if false response will be boolean indicating whether the request was successful.
     *
     * @return boolean|mixed|null True or xml_response if the transaction is successfully partially completed
     * or server output in xml format or false if error occurred.
     *
     * @throws \InvalidArgumentException
     */
    public function partiallyComplete($params = null, $xml_response = false)
    {
	    $sorted_params = $this->getParamsForPartialCompleteOrPartialRefund($params);

        $this->validatePartialCompleteOrPartialRefund($sorted_params);
        $response_xml = $this->request('partial_complete', $sorted_params);
        try {
            $response = new SimpleXMLElement($response_xml);
        } catch (\Exception $e) {
            $this->getClient()->getLogger()->error('String could not be parsed as XML');
        }

        if (isset($response) && $response->{'response-code'}[0] == "0" && $xml_response === false) {
            return true;
        } else if (isset($response) && $response->{'response-code'}[0] != "0" && $xml_response === false) {
            $this->getClient()->getLogger()->error($response_xml);

            return false;
        } else return $response_xml;
    }

    /**
     * Refund a transaction.
     *
     * @param array $params the parameters of the request.
     *
     * @param bool $xml_response if true response will be in xml with all details for the transaction,
     * if false response will be boolean indicating whether the request was successful.
     *
     * @return boolean|mixed|null True or xml_response if the transaction is successfully refunded
     * or server output in xml format or false if error occurred.
     *
     * @throws \InvalidArgumentException
     */
    public function refund($params = null, $xml_response = false)
    {
        $params['store_id'] = $this->getClient()->getStoreId();
        $params["hash"]     = $this->calculateHash([$params["order_number"], $params["store_id"]]);

        $this->validate($params);
        $response_xml = $this->request('refund', $params);
        try {
            $response = new SimpleXMLElement($response_xml);
        } catch (\Exception $e) {
            $this->getClient()->getLogger()->error('String could not be parsed as XML');
        }

        if (isset($response) && $response->{'response-code'}[0] == "0" && $xml_response === false) {
            return true;
        } else if (isset($response) && $response->{'response-code'}[0] != "0" && $xml_response === false) {
            $this->getClient()->getLogger()->error($response_xml);

            return false;
        } else return $response_xml;
    }

    /**
     * Partially refund a transaction.
     *
     * @param array $params the parameters of the request.
     *
     * @param bool $xml_response if true response will be in xml with all details for the transaction,
     * if false response will be boolean indicating whether the request was successful.
     *
     * @return boolean|mixed|null True or xml_response if the transaction is successfully partially refunded
     * or server output in xml format or false if error occurred.
     *
     * @throws \InvalidArgumentException
     */
    public function partiallyRefund($params = null, $xml_response = false)
    {
	    $sorted_params = $this->getParamsForPartialCompleteOrPartialRefund( $params );

        $this->validatePartialCompleteOrPartialRefund($sorted_params);
        $response_xml = $this->request('partial_refund', $sorted_params);
        try {
            $response = new SimpleXMLElement($response_xml);
        } catch (\Exception $e) {
            $this->getClient()->getLogger()->error('String could not be parsed as XML');
        }

        if (isset($response) && $response->{'response-code'}[0] == "0" && $xml_response === false) {
            return true;
        } else if (isset($response) && $response->{'response-code'}[0] != "0" && $xml_response === false) {
            $this->getClient()->getLogger()->error($response_xml);

            return false;
        } else return $response_xml;
    }

    /**
     * Cancel a preauthorized transaction.
     *
     * @param array $params the parameters of the request.
     *
     * @param bool $xml_response if true response will be in xml with all details for the transaction,
     * if false response will be boolean indicating whether the request was successful.
     *
     * @return boolean|mixed|null True or xml_response if the preauthorized transaction is successfully cancelled
     * or server output in xml format or false if error occurred.
     *
     * @throws \InvalidArgumentException
     */
    public function cancel($params = null, $xml_response = false)
    {
        $params['store_id'] = $this->getClient()->getStoreId();
        $params["hash"]     = $this->calculateHash([$params["order_number"], $params["store_id"]]);

        $this->validate($params);
        $response_xml = $this->request('cancel', $params);
        try {
            $response = new SimpleXMLElement($response_xml);
        } catch (\Exception $e) {
            $this->getClient()->getLogger()->error('String could not be parsed as XML');
        }

        if (isset($response) && $response->{'response-code'}[0] == "0" && $xml_response === false) {
            return true;
        } else if (isset($response) && $response->{'response-code'}[0] != "0" && $xml_response === false) {
            $this->getClient()->getLogger()->error($response_xml);

            return false;
        } else return $response_xml;
    }

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
        $sortedParams                  = [];
        $sortedParams['order_number']  = $params['order_number'];
        $sortedParams['store_id']      = $this->getClient()->getStoreId();
        $sortedParams['currency_code'] = CheckoutService::CURRENCY_CODES[ $params['currency_code'] ];
        $sortedParams['timestamp']     = date('YmdHis');
        $sortedParams['version']       = $this->getClient()->getApiVersion();
        $sortedParams["hash"]          = $this->calculateHash($sortedParams);

        $this->validateStatus($sortedParams);

        return $this->request('status', $sortedParams);
    }

    /**
     * Validate the parameters of the request.
     *
     * @param array $params the parameters of the request.
     *
     * @throws \InvalidArgumentException
     *
     */
    public function validateComplete($params = [])
    {
        $this->validate($params);

        if (isset($params["subscription"]) && ! in_array($params["subscription"], ["true", "false"])) {
            throw new \InvalidArgumentException('Invalid value for subscription.');
        }
        if (isset($params["account_id"]) && strlen($params["account_id"]) > 13) {
            throw new \InvalidArgumentException('The maximum length of account_id is 13.');
        }
    }

    /**
     * Validate the parameters of the request.
     *
     * @param array $params the parameters of the request.
     *
     * @throws \InvalidArgumentException
     *
     */
    public function validatePartialCompleteOrPartialRefund($params = [])
    {
        $this->validate($params);

        if ( ! isset($params["new_amount"]) || $params["new_amount"] === "") {
            throw new \InvalidArgumentException('The field new_amount is mandatory.');
        }

	    if ( ! isset($params["currency"]) || $params["currency"] === "") {
		    throw new \InvalidArgumentException('The field currency is mandatory.');
	    }

        if (preg_match("~^[0-9]+(\.[0-9]+)?$~xD", $params["new_amount"]) === 0) {
            throw new \InvalidArgumentException('Invalid value for new_amount.');
        }

	    if ( ! array_key_exists( $params["currency"], CheckoutService::CURRENCY_CODES ) ) {
		    throw new \InvalidArgumentException( 'Invalid value for currency.' );
	    }
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
        $this->validate($params);

        if ( ! isset($params["currency_code"]) || $params["currency_code"] === "") {
            throw new \InvalidArgumentException('The field currency_code is mandatory.');
        }
        if ( ! isset($params["timestamp"]) || $params["timestamp"] === "") {
            throw new \InvalidArgumentException('The field timestamp is mandatory.');
        }
        if ( ! isset($params["version"]) || $params["version"] === "") {
            throw new \InvalidArgumentException('The field version is mandatory.');
        }

        if ( ! in_array($params["currency_code"], CheckoutService::CURRENCY_CODES)) {
            throw new \InvalidArgumentException('Invalid value for currency_code.');
        }
    }

	/**
	 * Create the body for the request to neptunus from the parameters of the client`s request.
	 * It is used for partial complete and partial refund.
	 *
	 * @param array $params the parameters of the request.
	 *
	 * @return array the params for the neptunus request.
	 */
	public function getParamsForPartialCompleteOrPartialRefund($params = [])
	{
		$sorted_params                 = [];
		$sorted_params["order_number"] = $params["order_number"];
		$sorted_params["store_id"]     = $this->getClient()->getStoreId();
		$sorted_params["version"]      = $this->getClient()->getApiVersion();
		$sorted_params["new_amount"]   = $params["new_amount"];
		$sorted_params["currency"]     = $params["currency"];
		$sorted_params["hash"]         = $this->calculateHash( $sorted_params );

		return $sorted_params;
	}

}
