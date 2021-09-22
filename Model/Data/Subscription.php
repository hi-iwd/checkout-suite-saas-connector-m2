<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace IWD\CheckoutConnector\Model\Data;

use IWD\CheckoutConnector\Api\Data\SubscriptionInterface;

class Subscription extends \Magento\Framework\Api\AbstractExtensibleObject implements SubscriptionInterface
{
    /**
     * Get id
     * @return string|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Set id
     * @param string $id
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get sku
     * @return string|null
     */
    public function getSku()
    {
        return $this->_get(self::SKU);
    }

    /**
     * Set sku
     * @param string $sku
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * @return string|null
     */
    public function getPlanId()
    {
        return $this->_get(self::PLAN_ID);
    }

    /**
     * @param string $id
     * @return SubscriptionInterface
     */
    public function setPlanId($id)
    {
        return $this->setData(self::PLAN_ID, $id);
    }

    /**
     * @return string|null
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * @param string $id
     * @return SubscriptionInterface
     */
    public function setProductId($id)
    {
        return $this->setData(self::PRODUCT_ID, $id);
    }

    /**
     * @return string|null
     */
    public function getMerchantId()
    {
        return $this->_get(self::MERCHANT_ID);
    }

    /**
     * @param string $id
     * @return SubscriptionInterface
     */
    public function setMerchantId($id)
    {
        return $this->setData(self::MERCHANT_ID, $id);
    }

    /**
     * @return string|null
     */
    public function getCheckoutInstanceId()
    {
        return $this->_get(self::CHECKOUT_INSTANCE_ID);
    }

    /**
     * @param string $id
     * @return SubscriptionInterface
     */
    public function setCheckoutInstanceId($id)
    {
        return $this->setData(self::CHECKOUT_INSTANCE_ID, $id);
    }

    /**
     * @return string|null
     */
    public function getEnvironmentId()
    {
        return $this->_get(self::ENVIRONMENT_ID);
    }

    /**
     * @param string $id
     * @return SubscriptionInterface
     */
    public function setEnvironmentId($id)
    {
        return $this->setData(self::ENVIRONMENT_ID, $id);
    }

    /**
     * @return string|null
     */
    public function getEnv()
    {
        return $this->_get(self::ENV);
    }

    /**
     * @param string $env
     * @return SubscriptionInterface
     */
    public function setEnv($env)
    {
        return $this->setData(self::ENV, $env);
    }

    /**
     * @return string|null
     */
    public function getClientId()
    {
        return $this->_get(self::CLIENT_ID);
    }

    /**
     * @param string $id
     * @return SubscriptionInterface
     */
    public function setClientId($id)
    {
        return $this->setData(self::CLIENT_ID, $id);
    }

    /**
     * @return mixed|string|null
     */
    public function getActive()
    {
        return $this->_get(self::ACTIVE);
    }

    /**
     * @param string $active
     * @return SubscriptionInterface|Subscription
     */
    public function setActive($active)
    {
        return $this->setData(self::ACTIVE, $active);
    }

    /**
     * @return string|null
     */
    public function getQuantitySupported()
    {
        return $this->_get(self::QUANTITY_SUPPORTED);
    }

    /**
     * @param $quantitySupported
     * @return SubscriptionInterface
     */
    public function setQuantitySupported($quantitySupported)
    {
        return $this->setData(self::QUANTITY_SUPPORTED, $quantitySupported);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \IWD\CheckoutConnector\Api\Data\SubscriptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \IWD\CheckoutConnector\Api\Data\SubscriptionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}

