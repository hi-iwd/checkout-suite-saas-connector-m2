<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace IWD\CheckoutConnector\Api\Data;

interface SubscriptionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Subscription list.
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface[]
     */
    public function getItems();

    /**
     * Set sku list.
     * @param \IWD\CheckoutConnector\Api\Data\SubscriptionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

