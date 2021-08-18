<?php

namespace IWD\CheckoutConnector\Api;

/**
 * Interface PaymentRepositoryInterface
 *
 * @package IWD\CheckoutConnector\Api
 */
interface PaymentRepositoryInterface
{
    /**
     * Create or update a data
     */
    public function save($payment);

    /**
     * Get distributor by ID.
     */
    public function getById($entityId);

    /**
     * Delete payment information by ID.
     */
    public function deleteById($entityId);
}
