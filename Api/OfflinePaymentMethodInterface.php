<?php

namespace IWD\CheckoutConnector\Api;

/**
 * Interface OfflinePaymentMethodInterface
 *
 * @package IWD\CheckoutConnector\Api
 */
interface OfflinePaymentMethodInterface
{
    /**
     * @api
     * @param mixed $access_tokens
     * @return mixed[]|string
     */
    public function getShippingMethods($access_tokens);

    /**
     * @api
     * @param mixed $access_tokens
     * @return mixed[]|string
     */
    public function getOrderStatus($access_tokens);

    /**
     * @api
     * @param mixed $access_tokens
     * @return mixed[]|string
     */
    public function getGroups($access_tokens);
}
