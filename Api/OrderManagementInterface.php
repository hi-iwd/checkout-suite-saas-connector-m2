<?php

namespace IWD\CheckoutConnector\Api;

interface OrderManagementInterface
{
    /**
     * @param mixed
     * @return mixed[]|string
     */
    public function place($order);
}
