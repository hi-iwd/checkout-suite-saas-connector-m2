<?php

namespace IWD\CheckoutConnector\Helper;

use IWD\CheckoutConnector\Model\ResourceModel\PaymentMethod\CollectionFactory;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class PaymentMethod
 *
 * @package IWD\CheckoutConnector\Helper
 */
class PaymentMethod extends AbstractHelper
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var Invoice
     */
    private $invoice;

    /**'
     * @var Creditmemo
     */
    private $creditmemo;

    /**
     * @var CollectionFactory
     */
    private $paymentMethod;

    /**
     * @var IWDCheckoutPayConfigProvider
     */
    private $configProvider;

    /**
     * PaymentMethod constructor.
     *
     * @param Context $context
     * @param Http $request
     * @param Invoice $invoice
     * @param Creditmemo $creditmemo
     * @param CollectionFactory $paymentMethod
     * @param IWDCheckoutPayConfigProvider $configProvider
     */
    public function __construct(
        Context $context,
        Http $request,
        Invoice $invoice,
        Creditmemo $creditmemo,
        CollectionFactory $paymentMethod,
        IWDCheckoutPayConfigProvider $configProvider
    ) {
        parent::__construct($context);

        $this->request = $request;
        $this->paymentMethod = $paymentMethod;
        $this->invoice = $invoice;
        $this->creditmemo = $creditmemo;
        $this->configProvider = $configProvider;
    }

    /**
     * @return bool
     */
    public function updatePaymentTitle(){
        $action = $this->request->getFullActionName();
        $orderId = $this->request->getParam('order_id');

        if(!isset($orderId)) {
            if($invoiceId = $this->request->getParam('invoice_id')) {
                $orderId = $this->invoice->load($invoiceId)->getOrderId();
            } elseif ($creditmemoId = $this->request->getParam('creditmemo_id')) {
                $orderId = $this->creditmemo->load($creditmemoId)->getOrderId();
            }
        }

        $paymentMethod = $this->getCollection($orderId);

        if($paymentMethod->getSize()) {
            switch ($action){
                case 'sales_order_invoice_view':
                case 'sales_order_creditmemo_view':
                case 'sales_order_invoice_new':
                case 'sales_order_creditmemo_new':
                case 'sales_order_view':
                    return $paymentMethod->getFirstItem()->getPaymentMethod();
                    break;
            }
        }

        return false;
    }

    /**
     * @param $result
     * @return bool
     */
    public function updatePaymentMethodList($result) {
        $action  = $this->request->getFullActionName();
        $orderId = $this->request->getParam('order_id');
        $paymentMethodCode = $this->configProvider->getPaymentMethodCode();

        if($action == 'sales_order_view') {
            $paymentMethod = $this->getCollection($orderId);

            if($paymentMethod->getSize()) {
                if(isset($result[$paymentMethodCode])) {
                    if(gettype($result[$paymentMethodCode]) == 'string') {
                        $result[$paymentMethodCode] = $paymentMethod->getFirstItem()->getPaymentMethod();
                    } else {
                        $result[$paymentMethodCode]['label'] = $paymentMethod->getFirstItem()->getPaymentMethod();
                    }
                }
            }

            return $result;
        }

        return false;
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function getCollection($orderId){
        $paymentMethod = $this->paymentMethod->create();
        $paymentMethod->addFieldToFilter('order_id',array('eq' => $orderId));

        return $paymentMethod;
    }
}
