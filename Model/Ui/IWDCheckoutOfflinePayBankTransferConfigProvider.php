<?php

namespace IWD\CheckoutConnector\Model\Ui;

use IWD\CheckoutConnector\Model\OfflinePayment\BankTransfer;

/**
 * Class IWDCheckoutOfflinePayBankTransferConfigProvider
 *
 * @package IWD\CheckoutConnector\Model\Ui
 */
class IWDCheckoutOfflinePayBankTransferConfigProvider extends IWDCheckoutOfflineConfigProvider
{

	public $code = BankTransfer::PAYMENT_METHOD_BANKTRANSFER_CODE;

}