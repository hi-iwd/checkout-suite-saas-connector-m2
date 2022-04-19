<?php

namespace IWD\CheckoutConnector\Model\OfflinePayment;

/**
 * Class Zero
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 *
 * @api
 * @since 100.0.2
 */
class Zero extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_ZERO_CODE = 'iwd_checkout_offline_pay_zero';

    protected $_code = self::PAYMENT_METHOD_ZERO_CODE;

    protected $_infoBlockType = \IWD\CheckoutConnector\Block\Info\Zero::class;

    protected $_isOffline = true;
}
