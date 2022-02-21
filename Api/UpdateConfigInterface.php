<?php

namespace IWD\CheckoutConnector\Api;

/**
 * Interface UpdateConfigInterface
 *
 * @package IWD\CheckoutConnector\Api
 */
interface UpdateConfigInterface
{
    /**
     * @api
     * @param mixed $access_tokens
     * @param mixed $data
     * @return mixed[]|string
     */
    public function updateConfig($access_tokens, $data);
}