<?php

namespace IWD\CheckoutConnector\Model\Ui;

use IWD\CheckoutConnector\Model\OfflinePayment\PurchaseOrder;

/**
 * Class IWDCheckoutOfflinePayPurchaseOrderConfigProvider
 *
 * @package IWD\CheckoutConnector\Model\Ui
 */
class IWDCheckoutOfflinePayPurchaseOrderConfigProvider extends IWDCheckoutOfflineConfigProvider
{

	public $code = PurchaseOrder::PAYMENT_METHOD_PURCHASEORDER_CODE;

}