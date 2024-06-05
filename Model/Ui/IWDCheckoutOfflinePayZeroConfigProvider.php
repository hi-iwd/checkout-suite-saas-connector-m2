<?php

namespace IWD\CheckoutConnector\Model\Ui;

use IWD\CheckoutConnector\Model\OfflinePayment\Zero;

/**
 * Class IWDCheckoutOfflinePayZeroConfigProvider
 *
 * @package IWD\CheckoutConnector\Model\Ui
 */
class IWDCheckoutOfflinePayZeroConfigProvider extends IWDCheckoutOfflineConfigProvider
{

	public $code = Zero::PAYMENT_METHOD_ZERO_CODE;

}