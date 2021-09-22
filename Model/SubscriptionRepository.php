<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\Data\SubscriptionInterfaceFactory;
use IWD\CheckoutConnector\Api\Data\SubscriptionSearchResultsInterfaceFactory;
use IWD\CheckoutConnector\Api\SubscriptionRepositoryInterface;
use IWD\CheckoutConnector\Model\ResourceModel\Subscription as ResourceSubscription;
use IWD\CheckoutConnector\Model\ResourceModel\Subscription\CollectionFactory as SubscriptionCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{

    protected $resource;

    protected $subscriptionFactory;

    protected $subscriptionCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataSubscriptionFactory;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    private $searchCriteriaBuilder;

    /**
     * @param ResourceSubscription $resource
     * @param SubscriptionFactory $subscriptionFactory
     * @param SubscriptionInterfaceFactory $dataSubscriptionFactory
     * @param SubscriptionCollectionFactory $subscriptionCollectionFactory
     * @param SubscriptionSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceSubscription $resource,
        SubscriptionFactory $subscriptionFactory,
        SubscriptionInterfaceFactory $dataSubscriptionFactory,
        SubscriptionCollectionFactory $subscriptionCollectionFactory,
        SubscriptionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->resource = $resource;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataSubscriptionFactory = $dataSubscriptionFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \IWD\CheckoutConnector\Api\Data\SubscriptionInterface $subscription
    ) {
        /* if (empty($subscription->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $subscription->setStoreId($storeId);
        } */

        $subscriptionData = $this->extensibleDataObjectConverter->toNestedArray(
            $subscription,
            [],
            \IWD\CheckoutConnector\Api\Data\SubscriptionInterface::class
        );

        $subscriptionModel = $this->subscriptionFactory->create()->setData($subscriptionData);

        try {
            $this->resource->save($subscriptionModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the subscription: %1',
                $exception->getMessage()
            ));
        }
        return $subscriptionModel->getDataModel();
    }



    /**
     * {@inheritdoc}
     */
    public function checkAndSave(
        \IWD\CheckoutConnector\Api\Data\SubscriptionInterface $subscription
    ) {
        /* if (empty($subscription->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $subscription->setStoreId($storeId);
        } */

        $subscriptionData = $this->extensibleDataObjectConverter->toNestedArray(
            $subscription,
            [],
            \IWD\CheckoutConnector\Api\Data\SubscriptionInterface::class
        );

        $subscriptionModel = $this->subscriptionFactory->create()->setData($subscriptionData);
        $model = $this->getByMerchantIdAndSku($subscriptionModel);
        if (count($model) > 0) {
            $this->delete($model[0]);
        }
        try {
            $this->resource->save($subscriptionModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the subscription: %1',
                $exception->getMessage()
            ));
        }
        return $subscriptionModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($subscriptionId)
    {
        $subscription = $this->subscriptionFactory->create();
        $this->resource->load($subscription, $subscriptionId);
        if (!$subscription->getId()) {
            throw new NoSuchEntityException(__('Subscription with id "%1" does not exist.', $subscriptionId));
        }
        return $subscription->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->subscriptionCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \IWD\CheckoutConnector\Api\Data\SubscriptionInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByMerchantIdAndSku($subscription)
    {
        $this->searchCriteriaBuilder->addFilter('sku', $subscription->getSku());
        $this->searchCriteriaBuilder->addFilter('merchant_id', $subscription->getMerchantId());
        $this->searchCriteriaBuilder->setPageSize(1);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        return $this->getList($searchCriteria)->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \IWD\CheckoutConnector\Api\Data\SubscriptionInterface $subscription
    ) {
        try {
            $subscriptionModel = $this->subscriptionFactory->create();
            $this->resource->load($subscriptionModel, $subscription->getId());
            $this->resource->delete($subscriptionModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Subscription: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($subscriptionId)
    {
        return $this->delete($this->get($subscriptionId));
    }
}
