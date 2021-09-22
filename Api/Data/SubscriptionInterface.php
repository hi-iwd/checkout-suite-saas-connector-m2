<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace IWD\CheckoutConnector\Api\Data;

interface SubscriptionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ID = 'entity_id';
    const PLAN_ID = 'plan_id';
    const PRODUCT_ID = 'product_id';
    const SKU = 'sku';
    const MERCHANT_ID = 'merchant_id';
    const CHECKOUT_INSTANCE_ID = 'checkout_instance_id';
    const ENVIRONMENT_ID = 'environment_id';
    const ENV = 'env';
    const CLIENT_ID = 'client_id';
    const ACTIVE = 'active';
    const QUANTITY_SUPPORTED = 'quantity_supported';

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setEntityId($id);

    /**
     * Get id
     * @return string|null
     */
    public function getPlanId();

    /**
     * Set id
     * @param string $id
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setPlanId($id);

    /**
     * Get id
     * @return string|null
     */
    public function getProductId();

    /**
     * Set id
     * @param string $id
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setProductId($id);

    /**

     * @return string|null
     */
    public function getSku();

    /**

     * @param string $sku
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setSku($sku);

    /**

     * @return string|null
     */
    public function getMerchantId();

    /**

     * @param string $id
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setMerchantId($id);

    /**

     * @return string|null
     */
    public function getCheckoutInstanceId();

    /**

     * @param string $id
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setCheckoutInstanceId($id);

    /**

     * @return string|null
     */
    public function getEnvironmentId();

    /**

     * @param string $id
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setEnvironmentId($id);

    /**

     * @return string|null
     */
    public function getEnv();

    /**

     * @param string $env
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setEnv($env);

    /**

     * @return string|null
     */
    public function getClientId();

    /**

     * @param string $id
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setClientId($id);

    /**
     * @return string|null
     */
    public function getActive();

    /**

     * @param string $active
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setActive($active);

    /**
     * @return string|null
     */
    public function getQuantitySupported();

    /**

     * @param string $active
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionInterface
     */
    public function setQuantitySupported($quantitySupported);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \IWD\CheckoutConnector\Api\Data\SubscriptionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \IWD\CheckoutConnector\Api\Data\SubscriptionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \IWD\CheckoutConnector\Api\Data\SubscriptionExtensionInterface $extensionAttributes
    );
}
