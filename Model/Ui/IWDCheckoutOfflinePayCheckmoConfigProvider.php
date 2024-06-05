<?php

namespace IWD\CheckoutConnector\Model\Ui;

use IWD\CheckoutConnector\Model\OfflinePayment\Checkmo;

/**
 * Class IWDCheckoutOfflinePayCheckmoConfigProvider
 *
 * @package IWD\CheckoutConnector\Model\Ui
 */
class IWDCheckoutOfflinePayCheckmoConfigProvider extends IWDCheckoutOfflineConfigProvider
{

	public $code = Checkmo::PAYMENT_METHOD_CHECKMO_CODE;

}