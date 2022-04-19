<?php

namespace IWD\CheckoutConnector\Model\OfflinePayment;

/**
 * Class PurchaseOrder
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 *
 * @api
 * @since 100.0.2
 */
class PurchaseOrder extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_PURCHASEORDER_CODE = 'iwd_checkout_offline_pay_purchaseorder';

    protected $_code = self::PAYMENT_METHOD_PURCHASEORDER_CODE;

    protected $_infoBlockType = \IWD\CheckoutConnector\Block\Info\PurchaseOrder::class;

    protected $_isOffline = true;
}
