<?php

namespace IWD\CheckoutConnector\Block\Checkout;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Request\Http;
use Magento\Checkout\Model\Session;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCheckmoConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayZeroConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayPurchaseOrderConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayBankTransferConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCashOnDeliveryConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCustomConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflineMultiple;

/**
 * Class Success
 *
 * @package IWD\CheckoutConnector\Block\Checkout
 */
class Success extends Template
{
    private $request;
    private $checkoutSession;
    private $orderRepository;
    private $checkMoConfigProvider;
    private $zeroConfigProvider;
    private $purchaseOrderConfigProvider;
    private $bankTransferConfigProvider;
    private $cashOnDeliveryConfigProvider;
    private $customConfigProvider;
    private $multipleConfigProvider;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param Http $request
     * @param Session $checkoutSession
     * @param OrderRepository $orderRepository
     * @param IWDCheckoutOfflinePayCheckmoConfigProvider $checkMoConfigProvider
     * @param IWDCheckoutOfflinePayZeroConfigProvider $zeroConfigProvider
     * @param IWDCheckoutOfflinePayPurchaseOrderConfigProvider $purchaseOrderConfigProvider
     * @param IWDCheckoutOfflinePayBankTransferConfigProvider $bankTransferConfigProvider
     * @param IWDCheckoutOfflinePayCashOnDeliveryConfigProvider $cashOnDeliveryConfigProvider
     * @param IWDCheckoutOfflinePayCustomConfigProvider $customConfigProvider
     * @param IWDCheckoutOfflineMultiple $multipleConfigProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Http $request,
        Session $checkoutSession,
        OrderRepository $orderRepository,
        IWDCheckoutOfflinePayCheckmoConfigProvider $checkMoConfigProvider,
        IWDCheckoutOfflinePayZeroConfigProvider $zeroConfigProvider,
        IWDCheckoutOfflinePayPurchaseOrderConfigProvider $purchaseOrderConfigProvider,
        IWDCheckoutOfflinePayBankTransferConfigProvider $bankTransferConfigProvider,
        IWDCheckoutOfflinePayCashOnDeliveryConfigProvider $cashOnDeliveryConfigProvider,
        IWDCheckoutOfflinePayCustomConfigProvider $customConfigProvider,
        IWDCheckoutOfflineMultiple $multipleConfigProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->checkMoConfigProvider = $checkMoConfigProvider;
        $this->zeroConfigProvider = $zeroConfigProvider;
        $this->purchaseOrderConfigProvider = $purchaseOrderConfigProvider;
        $this->bankTransferConfigProvider = $bankTransferConfigProvider;
        $this->cashOnDeliveryConfigProvider = $cashOnDeliveryConfigProvider;
        $this->customConfigProvider = $customConfigProvider;
        $this->multipleConfigProvider = $multipleConfigProvider;
    }

    /**
     * @return array
     */
    public function getObj()
    {
        return $this->request->getParams();
    }

    /**
     * @return array|string[]
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getPaymentMethodDetails()
    {
        $orderId = $this->checkoutSession->getLastOrderId();
        $order = $this->loadOrderById($orderId);
        $payment = $order->getPayment();
        $paymentCode = $payment->getMethodInstance()->getCode();
        $paymentMethodDetails = $this->getPaymentMethodDetailsByCode($paymentCode, $payment);

        if($paymentCode === 'iwd_checkout_offline_pay_purchaseorder' || $paymentCode === 'iwd_checkout_multiple_payment') {
            if($payment->getPoNumber() && !empty($payment->getPoNumber())) {
                $key = $paymentCode === 'iwd_checkout_multiple_payment'
                    ? $this->multipleConfigProvider->getConfigData('field_name') : 'po_number';

                $paymentMethodDetails[$key] = $payment->getPoNumber();
            }
        }

        return $paymentMethodDetails;
    }

    /**
     * @param $id
     * @return OrderInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function loadOrderById($id)
    {
        return $this->orderRepository->get($id);
    }

    /**
     * @param $code
     * @param $payment
     * @return array|string[]
     */
    public function getPaymentMethodDetailsByCode($code, $payment)
    {
        switch ($code){
            case 'iwd_checkout_offline_pay_checkmo':
                $configProvider = $this->checkMoConfigProvider;
                break;
            case 'iwd_checkout_offline_pay_zero':
                $configProvider = $this->zeroConfigProvider;
                break;
            case 'iwd_checkout_offline_pay_purchaseorder':
                $configProvider = $this->purchaseOrderConfigProvider;
                break;
            case 'iwd_checkout_offline_pay_banktransfer':
                $configProvider = $this->bankTransferConfigProvider;
                break;
            case 'iwd_checkout_offline_pay_cashondelivery':
                $configProvider = $this->cashOnDeliveryConfigProvider;
                break;
            case 'iwd_checkout_offline_pay_custom':
                $configProvider = $this->customConfigProvider;
                break;
            case 'iwd_checkout_multiple_payment':
                $configProvider = $this->multipleConfigProvider;
                $configProvider->setCode($payment->getAdditionalInformation('iwd_method_code'));
                break;
            default:
                return [];
        }

        return [
            'title' => $configProvider->getConfigData('title') ?? '',
            'payable_to' => $configProvider->getConfigData('payable_to') ?? '',
            'mailing_address' => $configProvider->getConfigData('mailing_address') ?? '',
            'instruction' => $configProvider->getConfigData('instruction') ?? '',
            'extra_details' => $configProvider->getConfigData('extra_details') ?? '',
        ];
    }
}
