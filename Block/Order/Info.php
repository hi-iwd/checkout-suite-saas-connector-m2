<?php

namespace IWD\CheckoutConnector\Block\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use IWD\CheckoutConnector\Helper\PaymentMethod as IWDPaymentMethodHelper;

class Info extends \Magento\Sales\Block\Order\Info
{
    public $IWDPaymentMethodHelper;
    public $_isScopePrivate;

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
    public function getPaymentInfoHtml()
    {
        if($this->getOrder()->getPayment()->getMethod() == \IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider::CODE){
            $paymentMethod = $this->IWDPaymentMethodHelper->getCollection($this->getOrder()->getId());
            if($paymentMethod->getSize()){
                return $paymentMethod->getFirstItem()->getPaymentMethod();
            }
        }

        return $this->getChildHtml('payment_info');
    }
}
