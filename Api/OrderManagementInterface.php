<?php

namespace IWD\CheckoutConnector\Api;

interface OrderManagementInterface
{
    /**
     * @param mixed
     * @return array
     */
    public function place($order);
}
