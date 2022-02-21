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
     * @return mixed[]|string
     */
    public function getList($searchCriteria);
}
