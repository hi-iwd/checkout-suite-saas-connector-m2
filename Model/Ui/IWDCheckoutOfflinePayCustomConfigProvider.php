<?php

namespace IWD\CheckoutConnector\Model\Ui;

use IWD\CheckoutConnector\Model\OfflinePayment\Custom;

/**
 * Class IWDCheckoutOfflinePayCustomConfigProvider
 *
 * @package IWD\CheckoutConnector\Model\Ui
 */
class IWDCheckoutOfflinePayCustomConfigProvider extends IWDCheckoutOfflineConfigProvider
{

	public $code = Custom::PAYMENT_METHOD_CUSTOM_CODE;

}