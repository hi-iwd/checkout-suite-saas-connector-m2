<?php

namespace IWD\CheckoutConnector\Api;

/**
 * Interface ShippingMethodsInterface
 *
 * @package IWD\CheckoutConnector\Api
 */
interface OfflinePaymentMethodInterface
{
    /**
     * @api
     * @param mixed $access_tokens
     * @return array_iwd|string
     */
    public function getShippingMethods($access_tokens);

    /**
     * @api
     * @param mixed $access_tokens
     * @return array_iwd|string
     */
    public function getOrderStatus($access_tokens);
}
