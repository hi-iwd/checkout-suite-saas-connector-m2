<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\CheckoutConnector\Api\Data;

/**
 * Interface PaymentInterface
 *
 * @package IWD\CheckoutConnector\Api\Data
 */
interface PaymentInterface
{
    /**
     * get entity id
     */
    public function getEntityId();

    /**
     * get order id
     */
    public function getOrderId();

    /**
     * get payment method
     */
    public function getPaymentMethod();

    /**
     * set entity id
     */
    public function setEntityId();

    /**
     * set order id
     */
    public function setOrderId();

    /**
     * set payment method
     */
    public function setPaymentMethod();
}
