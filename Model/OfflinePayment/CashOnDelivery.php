<?php

namespace IWD\CheckoutConnector\Model\OfflinePayment;

/**
 * Class CashOnDelivery
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 *
 * @api
 * @since 100.0.2
 */
class CashOnDelivery extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_CASHONDELIVERY_CODE = 'iwd_checkout_offline_pay_cashondelivery';

    protected $_code = self::PAYMENT_METHOD_CASHONDELIVERY_CODE;

    protected $_infoBlockType = \IWD\CheckoutConnector\Block\Info\CashOnDelivery::class;

    protected $_isOffline = true;
}
