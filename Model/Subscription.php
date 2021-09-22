<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\Data\SubscriptionInterface;
use IWD\CheckoutConnector\Api\Data\SubscriptionInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Subscription extends \Magento\Framework\Model\AbstractModel
{

    protected $subscriptionDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'iwd_ubscription';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param SubscriptionInterfaceFactory $subscriptionDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \IWD\CheckoutConnector\Model\ResourceModel\Subscription $resource
     * @param \IWD\CheckoutConnector\Model\ResourceModel\Subscription\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        SubscriptionInterfaceFactory $subscriptionDataFactory,
        DataObjectHelper $dataObjectHelper,
        \IWD\CheckoutConnector\Model\ResourceModel\Subscription $resource,
        \IWD\CheckoutConnector\Model\ResourceModel\Subscription\Collection $resourceCollection,
        array $data = []
    ) {
        $this->subscriptionDataFactory = $subscriptionDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve subscription model with subscription data
     * @return SubscriptionInterface
     */
    public function getDataModel()
    {
        $subscriptionData = $this->getData();
        
        $subscriptionDataObject = $this->subscriptionDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $subscriptionDataObject,
            $subscriptionData,
            SubscriptionInterface::class
        );
        
        return $subscriptionDataObject;
    }
}

