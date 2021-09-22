<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace IWD\CheckoutConnector\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface SubscriptionRepositoryInterface
{

    /**
     * Save Subscription
     * @param \IWD\CheckoutConnector\Api\Data\SubscriptionInterface $subscription
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \IWD\CheckoutConnector\Api\Data\SubscriptionInterface $subscription
    );
    /**
     * Save Subscription
     * @param \IWD\CheckoutConnector\Api\Data\SubscriptionInterface $subscription
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkAndSave(
        \IWD\CheckoutConnector\Api\Data\SubscriptionInterface $subscription
    );

    /**
     * Retrieve Subscription
     * @param string $subscriptionId
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($subscriptionId);

    /**
     * Retrieve Subscription matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Subscription
     * @param \IWD\CheckoutConnector\Api\Data\SubscriptionInterface $subscription
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \IWD\CheckoutConnector\Api\Data\SubscriptionInterface $subscription
    );

    /**
     * Delete Subscription by ID
     * @param string $subscriptionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($subscriptionId);
}
