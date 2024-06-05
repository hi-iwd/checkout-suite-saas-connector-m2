<?php

namespace IWD\CheckoutConnector\Model\Ui;

use IWD\CheckoutConnector\Model\OfflinePayment\CashOnDelivery;

/**
 * Class IWDCheckoutOfflinePayCashOnDeliveryConfigProvider
 *
 * @package IWD\CheckoutConnector\Model\Ui
 */
class IWDCheckoutOfflinePayCashOnDeliveryConfigProvider extends IWDCheckoutOfflineConfigProvider
{

	public $code = CashOnDelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE;

}