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
class BankTransfer extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_BANKTRANSFER_CODE = 'iwd_checkout_offline_pay_banktransfer';

    protected $_code = self::PAYMENT_METHOD_BANKTRANSFER_CODE;

    protected $_infoBlockType = \IWD\CheckoutConnector\Block\Info\BankTransfer::class;

    protected $_isOffline = true;
}
