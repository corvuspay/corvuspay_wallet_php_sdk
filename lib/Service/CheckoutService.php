<?php

namespace CorvusPay\Service;

class CheckoutService extends AbstractService
{
    /**
     * CorvusPay Checkout URLs for test and production.
     */
    const CHECKOUT_URL = [
        'prod' => 'https://wallet.corvuspay.com/checkout/',
        'test' => 'https://wallet.test.corvuspay.com/checkout/'
    ];

    /**
     * List of languages supported by CorvusPay. ISO 639-1 codes (almost).
     */
    const SUPPORTED_LANGUAGES = array(
        'de' => 'German',
        'en' => 'English',
        'hr' => 'Croatian',
        'it' => 'Italian',
        'sr' => 'Serbian',
        'sl' => 'Slovenian',
    );

    /**
     * Currency codes conversion. ISO 4217 codes.
     */
    const CURRENCY_CODES = array(
        'AUD' => '036', /* Australian dollar, 100 */
        'BAM' => '977', /* Bosnia and Herzegovina convertible mark, 100 */
        'CAD' => '124', /* Canadian dollar, 100 */
        'CHF' => '756', /* Swiss franc, 100 */
        'CZK' => '203', /* Czech koruna, 100 */
        'DKK' => '208', /* Danish krone, 100 */
        'EUR' => '978', /* Euro, 100 */
        'GBP' => '826', /* British pound, 100 */
        'HRK' => '191', /* Croatian kuna, 100 */
        'HUF' => '348', /* Hungarian forint, 100 */
        'NOK' => '578', /* Norwegian krone, 100 */
        'PLN' => '985', /* Polish złoty, 100 */
        'RSD' => '941', /* Serbian dinar, 100 */
        'USD' => '840', /* United States dollar, 100 */
        'SEK' => '752', /* Swedish krona, 100 */
        'BHD' => '048', /* Bahraini dinar, 100 */
        'RUB' => '643', /* Russian ruble, 100 */
        'RON' => '946', /* Romanian leu, 100 */
        'ISK' => '352', /* Icelandic króna, 100 */
        'MKD' => '807', /* Macedonian denar, 100 */
    );

    /**
     * Card brands.
     */
    const CARD_BRANDS = array(
        'amex'     => 'American Express',
        'diners'   => 'Diners Club',
        'discover' => 'Discover Card',
        'maestro'  => 'Maestro',
        'master'   => 'Mastercard',
        'visa'     => 'Visa',
        'dina'     => 'DinaCard',
        'jcb'      => 'JCB',
    );

    /**
     * Available tabs in checkout form.
     */
	const TABS = array(
		'checkout'    => 'Card payment',
		'pis'         => 'Pay by IBAN',
		'wallet'      => 'Quick wallet payment',
		'paysafecard' => 'paysafecard',
		'applepay'    => 'Apple Pay',
		'googlepay'   => 'Google Pay',
	);

    /**
     * Function that redirects to CorvusPay checkout page.
     *
     * @param array $params the parameters of the request.
     *
     * @param string $type the name of the button or if the value is 'auto' then redirect to checkout page.
     *
     * @param bool $echo if true echo the html form, if false return the html as a string.
     *
     * @return string the generated HTML form.
     * @throws \InvalidArgumentException
     */
    public function create($params = [], $type = 'Checkout', $echo = true)
    {
        $additional_required_params = [
            'store_id'         => $this->getClient()->getStoreId(),
            'require_complete' => 'false',
            'version'          => $this->getClient()->getApiVersion()
        ];
        $params                     = array_merge($additional_required_params, $params);
        $params                     = array_merge($params, ['signature' => $this->calculateSignature($params)]);

        //Validation
        $this->validate($params);

        $link = self::CHECKOUT_URL[ $this->client->getEnvironment() ];

        if ($echo) {
            $this->echoHtmlForm($params, $type, $link);

            return "";
        } else
            return $this->returnHtmlForm($params, $type, $link);
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
        if ( ! isset($params["language"]) || $params["language"] === "") {
            throw new \InvalidArgumentException('The field language is mandatory.');
        }
        if ( ! isset($params["currency"]) || $params["currency"] === "") {
            throw new \InvalidArgumentException('The field currency is mandatory.');
        }
        if ( ! isset($params["amount"]) || $params["amount"] === "") {
            throw new \InvalidArgumentException('The field amount is mandatory.');
        }
        if ( ! isset($params["cart"]) || $params["cart"] === "") {
            throw new \InvalidArgumentException('The field cart is mandatory.');
        }
        if ( ! isset($params["signature"]) || $params["signature"] === "") {
            throw new \InvalidArgumentException('The field signature is mandatory.');
        }
        if ( ! isset($params["require_complete"]) || $params["require_complete"] === "") {
            throw new \InvalidArgumentException('The field require_complete is mandatory.');
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
        if ( ! array_key_exists($params["language"], self::SUPPORTED_LANGUAGES)) {
            throw new \InvalidArgumentException('Invalid value for language.');
        }
        if ( ! array_key_exists($params["currency"], self::CURRENCY_CODES)) {
            throw new \InvalidArgumentException('Invalid value for currency.');
        }
        if (preg_match("~^[0-9]+(\.[0-9]+)?$~xD", $params["amount"]) === 0) {
            throw new \InvalidArgumentException('Invalid value for amount.');
        }
        if ( ! in_array($params["require_complete"], ["true", "false"])) {
            throw new \InvalidArgumentException('Invalid value for require_complete.');
        }
        if (isset($params["subscription"]) && ! in_array($params["subscription"], ["true", "false"])) {
            throw new \InvalidArgumentException('Invalid value for subscription.');
        }
        if (isset($params["number_of_installments"]) && (preg_match("/^[0-9][1-9]$/i", $params["number_of_installments"]) === 0)) {
            throw new \InvalidArgumentException('Invalid value for number_of_installments.');
        }
        if (isset($params["payment_all"]) && $params["payment_all"] !== "Y0299") {
            throw new \InvalidArgumentException('Invalid value for payment_all.');
        }
        if (isset($params["payment_all_dynamic"]) && ! in_array($params["payment_all_dynamic"], ["true", "false"])) {
            throw new \InvalidArgumentException('Invalid value for payment_all_dynamic.');
        }
        if (isset($params["payment_amex"]) && (preg_match("/^[Y,N][0-9][0-9][0-9][0-9]$/", $params["payment_amex"]) === 0)) {
            throw new \InvalidArgumentException('Invalid value for payment_amex.');
        }
        if (isset($params["payment_diners"]) && (preg_match("/^[Y,N][0-9][0-9][0-9][0-9]$/", $params["payment_diners"]) === 0)) {
            throw new \InvalidArgumentException('Invalid value for payment_diners.');
        }
        if (isset($params["payment_visa"]) && (preg_match("/^[Y,N][0-9][0-9][0-9][0-9]$/", $params["payment_visa"]) === 0)) {
            throw new \InvalidArgumentException('Invalid value for payment_visa.');
        }
        if (isset($params["payment_master"]) && (preg_match("/^[Y,N][0-9][0-9][0-9][0-9]$/", $params["payment_master"]) === 0)) {
            throw new \InvalidArgumentException('Invalid value for payment_master.');
        }
        if (isset($params["payment_maestro"]) && (preg_match("/^[Y,N][0-9][0-9][0-9][0-9]$/", $params["payment_maestro"]) === 0)) {
            throw new \InvalidArgumentException('Invalid value for payment_maestro.');
        }
        if (isset($params["payment_discover"]) && (preg_match("/^[Y,N][0-9][0-9][0-9][0-9]$/", $params["payment_discover"]) === 0)) {
            throw new \InvalidArgumentException('Invalid value for payment_discover.');
        }
        if (isset($params["payment_jcb"]) && (preg_match("/^[Y,N][0-9][0-9][0-9][0-9]$/", $params["payment_jcb"]) === 0)) {
            throw new \InvalidArgumentException('Invalid value for payment_jcb.');
        }
        if (isset($params["payment_dina"]) && (preg_match("/^[Y,N][0-9][0-9][0-9][0-9]$/", $params["payment_dina"]) === 0)) {
            throw new \InvalidArgumentException('Invalid value for payment_dina.');
        }
        if (isset($params["cc_type"]) && ! array_key_exists($params["cc_type"], self::CARD_BRANDS)) {
            throw new \InvalidArgumentException('Invalid value for cc_type.');
        }
        if (isset($params["hide_tabs"]) && ! array_key_exists($params["hide_tabs"], self::TABS)) {
            $array_tabs = explode(",", $params["hide_tabs"]);
            foreach ($array_tabs as $tab) {
                if ( ! array_key_exists($tab, self::TABS))
                    throw new \InvalidArgumentException('Invalid value for hide_tabs.');
            }
        }
        if (isset($params["discount_amount"]) && preg_match("~^[0-9]+(\.[0-9]+)?$~xD", $params["discount_amount"]) === 0) {
            throw new \InvalidArgumentException('Invalid value for discount_amount.');
        }

    }

    /**
     * Function that generates and echo the payment form with the given parameters.
     *
     * @param array $params the parameters of the request.
     *
     * @param string $type the name of the button or if the value is 'auto' then redirect to checkout page.
     *
     * @param string $link endpoint where to redirect.
     */
    private function echoHtmlForm($params = [], $type = 'Checkout', $link = "")
    {
        echo("<form id='corvuspay' method='post' action='$link'>");
        foreach ($params as $key => $value) {
            $key = htmlentities($key,ENT_QUOTES);
            $value = htmlentities($value,ENT_QUOTES);
            echo("<input type='hidden' name='$key' value='$value'/>");
        }

        if ($type == 'auto') {
            echo '<input type="submit" value="Click here if you are not redirected automatically" /></form>';
            echo '<script type="text/javascript">document.getElementById("corvuspay").submit();</script>';
        } else {
            echo '<input type="submit" value="' . $type . '" />';
            echo '</form>';
        }
    }

    /**
     * Function that generates and echo the payment form with the given parameters.
     *
     * @param array $params the parameters of the request.
     *
     * @param string $type the name of the button or if the value is 'auto' then redirect to checkout page.
     *
     * @param string $link endpoint where to redirect.
     *
     * @return string the generated HTML form.
     */
    private function returnHtmlForm($params = [], $type = 'Checkout', $link = "")
    {
        $html = "<form id='corvuspay' method='post' action='$link'>";
        foreach ($params as $key => $value) {
            $html .= '<input type="hidden" name="' .  htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '"/>';
        }

        if ($type == 'auto') {
            $html .= '<input type="submit" value="Click here if you are not redirected automatically" /></form>';
            $html .= '<script type="text/javascript">document.getElementById("corvuspay").submit();</script>';
        } else {
            $html .= '<input type="submit" value="' . $type . '" />';
            $html .= '</form>';
        }

        return $html;
    }

}
