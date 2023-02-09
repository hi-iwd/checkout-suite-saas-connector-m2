<?php

namespace IWD\CheckoutConnector\Block\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use IWD\CheckoutConnector\Helper\PaymentMethod as IWDPaymentMethodHelper;

/**
 * Class Info
 *
 * @package IWD\CheckoutConnector\Block\Order
 */
class Info extends \Magento\Sales\Block\Order\Info
{

	/**
	 * @var IWDPaymentMethodHelper
	 */
    public $IWDPaymentMethodHelper;

	/**
	 * @var bool
	 */
    public $_isScopePrivate;

	/**
	 * @param  TemplateContext  $context
	 * @param  Registry  $registry
	 * @param  PaymentHelper  $paymentHelper
	 * @param  AddressRenderer  $addressRenderer
	 * @param  IWDPaymentMethodHelper  $IWDPaymentMethodHelper
	 * @param  array  $data
	 */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        PaymentHelper $paymentHelper,
        AddressRenderer $addressRenderer,
        IWDPaymentMethodHelper $IWDPaymentMethodHelper,
        array $data = []
    ) {
        $this->_isScopePrivate = true;
        $this->IWDPaymentMethodHelper = $IWDPaymentMethodHelper;

        parent::__construct($context,$registry,$paymentHelper,$addressRenderer,$data);
    }

	/**
	 * @return string
	 */
	public function getPaymentInfoHtml()
    {
        if($this->getOrder()->getPayment()->getMethod() === IWDCheckoutPayConfigProvider::CODE){
            $paymentMethod = $this->IWDPaymentMethodHelper->getCollection('entity_id', $this->getOrder()->getPayment()->getEntityId());

            if($paymentMethod->getSize()){
	            $additionalInfo = $paymentMethod->getFirstItem()->getAdditionalInformation();
	            $paymentBlockHtml = $additionalInfo['iwd_method_title'];

	            if (isset($additionalInfo['iwd_additional_info'])) {
		            $paymentBlockHtml .= $this->IWDPaymentMethodHelper->getAdditionalInfoHtml($additionalInfo['iwd_additional_info']);
	            }

	            return $paymentBlockHtml;
            }
        }

        return $this->getChildHtml('payment_info');
    }
}
