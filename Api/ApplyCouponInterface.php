<?php

namespace IWD\CheckoutConnector\Api;

/**
 * Interface ApplyCouponInterface
 *
 * @package IWD\CheckoutConnector\Api
 */
interface ApplyCouponInterface
{
    /**
     * @api
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param mixed $data
     * @return mixed[]|string
     */
    public function getData($quote_id, $access_tokens, $data = null);
}
