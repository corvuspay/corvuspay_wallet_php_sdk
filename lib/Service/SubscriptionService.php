<?php

namespace CorvusPay\Service;

use SimpleXMLElement;

class SubscriptionService extends AbstractService
{

    /**
     * Charging the next subscription payment.
     *
     * @param  array  $params        the parameters of the request.
     *
     * @param  bool   $xml_response  if true response will be in xml with all
     *                               details for the transaction, if false
     *                               response will be boolean indicating
     *                               whether the request was successful.
     *
     * @return boolean|mixed|null True or xml_response if payment is successful
     *                            or server output in xml format or false if
     *                            error occurred.
     *
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function pay($params = null, $xml_response = false)
    {
	    $sorted_params                 = [];
	    $sorted_params["order_number"] = $params["order_number"];
	    $sorted_params["store_id"]     = $this->getClient()->getStoreId();
	    $sorted_params["version"]      = $this->getClient()->getApiVersion();
	    if ( isset( $params['new_amount'] ) ) {
		    $sorted_params["new_amount"] = $params["new_amount"];
	    }
	    if ( isset( $params['currency'] ) ) {
		    $sorted_params["currency"] = $params["currency"];
	    }
	    $sorted_params["hash"]         = $this->calculateHash( $sorted_params );
	    $sorted_params['subscription'] = 'true';
	    $sorted_params["account_id"]   = $params["account_id"];
	    if ( isset( $params['cart'] ) ) {
		    $sorted_params["cart"] = $params["cart"];
	    }

	    $this->validateNextSubscriptionPayment( $sorted_params );
	    $response_xml = $this->request( 'next_sub_payment', $sorted_params );
        $response = new SimpleXMLElement($response_xml);

        if (isset($response) && $response->{'response-code'}[0] == "0" && $xml_response === false) {
            return true;
        } else if (isset($response) && $response->{'response-code'}[0] != "0" && $xml_response === false) {
            $this->getClient()->getLogger()->error($response_xml);

            return false;
        } else return $response_xml;
    }

    /**
     * Validate the parameters of the request.
     *
     * @param array $params the parameters of the request.
     *
     * @throws \InvalidArgumentException
     *
     */
    public function validateNextSubscriptionPayment($params = [])
    {
        $this->validate($params);

        if ( ! isset($params["subscription"]) || $params["subscription"] === "") {
            throw new \InvalidArgumentException('The field subscription is mandatory.');
        }
        if ( ! isset($params["account_id"]) || $params["account_id"] === "") {
            throw new \InvalidArgumentException('The field account_id is mandatory.');
        }
        if ( ! isset($params["version"]) || $params["version"] === "") {
            throw new \InvalidArgumentException('The field version is mandatory.');
        }
	    if ( ( ! isset( $params["new_amount"] ) || $params["new_amount"] === "" ) && ( isset( $params["currency"] ) && $params["currency"] != "" ) ) {
		    throw new \InvalidArgumentException( 'When you send currency, you must send the new_amount as well.' );
	    }

	    if ( ( ! isset( $params["currency"] ) || $params["currency"] === "" ) && ( isset( $params["new_amount"] ) && $params["new_amount"] != "" ) ) {
		    throw new \InvalidArgumentException( 'When you send new_amount, you must send the currency as well.' );
	    }

        if (! in_array($params["subscription"], ["true", "false"])) {
            throw new \InvalidArgumentException('Invalid value for subscription.');
        }
        if (strlen($params["account_id"]) > 13) {
            throw new \InvalidArgumentException('The maximum length of account_id is 13.');
        }

	    if ( isset( $params["new_amount"] ) && preg_match( "~^[0-9]+(\.[0-9]+)?$~xD", $params["new_amount"] ) === 0 ) {
		    throw new \InvalidArgumentException( 'Invalid value for new_amount.' );
	    }
	    if ( isset( $params["currency"] ) && ! array_key_exists( $params["currency"], CheckoutService::CURRENCY_CODES ) ) {
		    throw new \InvalidArgumentException( 'Invalid value for currency.' );
	    }
    }

}