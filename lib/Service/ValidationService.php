<?php

namespace CorvusPay\Service;

class ValidationService extends AbstractService
{
    /**
     * Validate the signature.
     *
     * @param array $params the parameters of the response from success url.
     *
     * @return bool true if the signature is valid and false if it is not.
     */
    public function signature($params = [])
    {
        $signature = $params["signature"];
        unset($params["signature"]);

        return $signature === $this->calculateSignature($params);
    }
}