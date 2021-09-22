<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace IWD\CheckoutConnector\Model\OfflinePayment;

/**
 * Class Checkmo
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 *
 * @api
 * @since 100.0.2
 */
class Checkmo extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_CHECKMO_CODE = 'iwd_checkout_offline_pay_checkmo';

    protected $_code = self::PAYMENT_METHOD_CHECKMO_CODE;

    protected $_infoBlockType = \IWD\CheckoutConnector\Block\Info\Checkmo::class;

    protected $_isOffline = true;
}
