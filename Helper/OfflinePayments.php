<?php

namespace IWD\CheckoutConnector\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCashOnDeliveryConfigProvider as CashOnDeliveryConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCheckmoConfigProvider as CheckmoConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayZeroConfigProvider as ZeroConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayBankTransferConfigProvider as BankTransferConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayPurchaseOrderConfigProvider as PurchaseOrderConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCustomConfigProvider as CustomConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflineMultiple as MultipleConfigProvider;

/**
 * Class Data
 *
 * @package IWD\CheckoutConnector\Helper
 */
class OfflinePayments extends AbstractHelper
{
    /**
     * @var CashOnDeliveryConfigProvider
     */
    private $cashOnDeliveryConfigProvider;

    /**
     * @var CheckmoConfigProvider
     */
    private $checkMoneyOrderConfigProvider;

    /**
     * @var ZeroConfigProvider
     */
    private $zeroConfigProvider;

    /**
     * @var BankTransferConfigProvider
     */
    private $bankTransferConfigProvider;

    /**
     * @var PurchaseOrderConfigProvider
     */
    private $purchaseOrderConfigProvider;

    /**
     * @var CustomConfigProvider
     */
    private $customConfigProvider;

    /**
     * @var MultipleConfigProvider
     */
    private $multipleConfigProvider;

    /**
     * OfflinePayments constructor.
     *
     * @param Context $context
     * @param CashOnDeliveryConfigProvider $cashOnDeliveryConfigProvider
     * @param CheckmoConfigProvider $checkmoConfigProvider
     * @param ZeroConfigProvider $zeroConfigProvider
     * @param BankTransferConfigProvider $bankTransferConfigProvider
     * @param PurchaseOrderConfigProvider $purchaseOrderConfigProvider
     * @param CustomConfigProvider $customConfigProvider
     * @param MultipleConfigProvider $multipleConfigProvider
     */
    public function __construct(
        Context $context,
        CashOnDeliveryConfigProvider $cashOnDeliveryConfigProvider,
        CheckmoConfigProvider $checkmoConfigProvider,
        ZeroConfigProvider $zeroConfigProvider,
        BankTransferConfigProvider $bankTransferConfigProvider,
        PurchaseOrderConfigProvider $purchaseOrderConfigProvider,
        CustomConfigProvider $customConfigProvider,
        MultipleConfigProvider $multipleConfigProvider
    ) {
        $this->cashOnDeliveryConfigProvider = $cashOnDeliveryConfigProvider;
        $this->checkMoneyOrderConfigProvider = $checkmoConfigProvider;
        $this->zeroConfigProvider = $zeroConfigProvider;
        $this->bankTransferConfigProvider = $bankTransferConfigProvider;
        $this->purchaseOrderConfigProvider = $purchaseOrderConfigProvider;
        $this->customConfigProvider = $customConfigProvider;
        $this->multipleConfigProvider = $multipleConfigProvider;

        parent::__construct($context);
    }

    /**
     * @param $code
     * @return mixed|null
     */
    public function getConfigProvider($code)
    {
        $method = $this->parseMethodCode($code);

        switch ($method) {
            case 'cash_on_delivery':
                return $this->cashOnDeliveryConfigProvider;
            case 'check_or_money_order':
                return $this->checkMoneyOrderConfigProvider;
            case 'zero':
                return $this->zeroConfigProvider;
            case 'banktransfer':
                return $this->bankTransferConfigProvider;
            case 'purchaseorder':
                return $this->purchaseOrderConfigProvider;
            case 'custom':
                return $this->customConfigProvider;
            case 'multiple_offline':
                return $this->multipleConfigProvider;
            default:
                return null;
        }
    }

    /**
     * @param $method
     * @param $data
     * @return string
     */
    public function getPoNumber($method, $data)
    {
        $poNumber = null;

        if($method === 'iwd_checkout_offline_pay_purchaseorder' && isset($data['po_number']) && $data['po_number']) {
            $poNumber = $data['po_number'];
        } elseif ($method === 'iwd_checkout_multiple_payment' && isset($data['multiple_field']) && $data['multiple_field']) {
            $poNumber = $data['multiple_field'];
        }

        return $poNumber;
    }

    /**
     * @param $code
     * @return string
     */
    public function parseMethodCode($code)
    {
        if (strpos($code, 'multiple_offline') !== false) {
            return 'multiple_offline';
        }

        return $code;
    }
}
