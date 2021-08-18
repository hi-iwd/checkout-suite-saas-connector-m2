<?php


namespace IWD\CheckoutConnector\Api;

interface TransactionInformationInterface
{
    /**
     * Estimate shipping methods and calculate quote totals based on address.
     *
     * @param  string  $cartId
     * @param mixed $data
     * @return array
     */
    public function calculate($cartId, $data );
}
