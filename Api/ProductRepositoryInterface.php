<?php

namespace IWD\CheckoutConnector\Api;

/**
 * Interface ProductRepositoryInterface
 *
 * @package IWD\CheckoutConnector\Api
 */
interface ProductRepositoryInterface
{
    /**
     * @api
     * @param string $searchCriteria
     * @return array
     */
    public function getList($searchCriteria);
}
