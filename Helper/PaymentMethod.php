<?php

namespace IWD\CheckoutConnector\Helper;

use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory;
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
    private $paymentCollectionFactory;

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
     * @param CollectionFactory $paymentCollectionFactory
     * @param IWDCheckoutPayConfigProvider $configProvider
     */
    public function __construct(
        Context $context,
        Http $request,
        Invoice $invoice,
        Creditmemo $creditmemo,
        CollectionFactory $paymentCollectionFactory,
        IWDCheckoutPayConfigProvider $configProvider
    ) {
        parent::__construct($context);

        $this->request = $request;
	    $this->paymentCollectionFactory = $paymentCollectionFactory;
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

        $paymentMethod = $this->getCollection('parent_id', $orderId);

        if($paymentMethod->getSize()) {
            switch ($action){
                case 'sales_order_invoice_view':
                case 'sales_order_creditmemo_view':
                case 'sales_order_invoice_new':
                case 'sales_order_creditmemo_new':
                case 'sales_order_view':
                    return $paymentMethod->getFirstItem()->getAdditionalInformation()['iwd_method_title'];
            }
        }

        return false;
    }

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
    public function getCollection($key, $value)
    {
        $paymentMethod = $this->paymentCollectionFactory->create();
        $paymentMethod->addFieldToFilter($key, ['eq' => $value]);

        return $paymentMethod;
    }

	/**
	 * @param $additionalInfo
	 *
	 * @return string
	 */
	public function getAdditionalInfoHtml($additionalInfo)
	{
		$html = '<br><br>';

		foreach($additionalInfo as $title => $value) {
			$html .= '<b>'.__($title).': </b>'.$value.'<br>';
		}

		return $html;
	}

	/**
	 * @param $additionalInfo
	 *
	 * @return string
	 */
	public function getAdditionalNotificationInfoHtml($additionalInfo)
	{
		$method = $additionalInfo['iwd_method_code'];
		$info = $additionalInfo['iwd_additional_info'];
		$tdStyle = 'width: 50%; padding: 2px 2px 2px 0;';

		$html = '<h3 style="margin-top: 30px">'.__('Payment Information').'</h3>';
		$html .= '<table style="width: 100%">';

		foreach($info as $title => $value) {
			$html .= '<tr>';
			$html .= '<td style="'.$tdStyle.'"><b>'.__($title).'</b></td>';
			$html .= '<td style="'.$tdStyle.'">'.$value.'</td>';
			$html .= '</tr>';

			if ($title === 'Payment Reference') {
				$html .= '<tr><td style="padding-bottom: 20px"></td></tr>';
			}
		}

		$html .= '</table>';

		if ($method === 'pay_upon_invoice') {
			$message = 'Please also note that our company has assigned the due purchase price claim from your order '
			           .'including any ancillary claims to Ratepay GmBH. The owner of the claim is thus Ratepay GmBH. '
			           .'A debt-discharging service is only possible to Ratepay GmBH, stating the purpose of use. '
			           .'The additional terms and conditions and the data protection notice of Ratepay GmBH apply:';

			$html .= '<p style="margin-top: 20px; font-size: 11px">';
			$html .= __($message);
			$html .= '<br><a href="https://www.retepay.com/legal/" target="_blank">https://www.retepay.com/legal/</a>';
			$html .= '</p>';

			$html .= '<h4 style="margin: 30px 0 10px;">'.__('Questions About Payment').'</h4>';
			$html .= '<table style="width: 100%">';
			$html .= '<tr>';
			$html .= '<td style="'.$tdStyle.'">'.__('Online').'</td>';
			$html .= '<td style="'.$tdStyle.'"><a href="https://myratepay.com" target="_blank">myratepay.com</a></td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td style="'.$tdStyle.'">'.__('Email').'</td>';
			$html .= '<td style="'.$tdStyle.'"><a href="mailto:payment@ratepay.com">payment@ratepay.com</a></td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td style="'.$tdStyle.'">'.__('Telephone').'</td>';
			$html .= '<td style="'.$tdStyle.'"><a href="tel:+4930983208620">+49 30 9832086 20</a></td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td style="'.$tdStyle.'">'.__('Monday - Friday').'</td>';
			$html .= '<td style="'.$tdStyle.'">08:00 - 19:00 UHR</td>';
			$html .= '</tr>';
			$html .= '</table>';
		}

		return $html;
	}
}
