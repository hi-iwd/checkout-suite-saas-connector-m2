<?php

namespace IWD\CheckoutConnector\Api;

/**
 * Interface ShippingMethodsInterface
 *
 * @package IWD\CheckoutConnector\Api
 */
interface ShippingMethodsInterface
{
    /**
     * @api
     * @param mixed $access_tokens
     * @return array_iwd|string
     */
    public function getShippingMethods($access_tokens);
}