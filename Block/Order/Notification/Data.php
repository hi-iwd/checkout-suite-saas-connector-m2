<?php

namespace IWD\CheckoutConnector\Block\Order\Notification;

class Data extends \Magento\Payment\Helper\Data
{
    public $IWDPaymentMethodHelper;
    public $paymentCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\Initial $initialConfig,
        \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory,
        \IWD\CheckoutConnector\Helper\PaymentMethod $IWDPaymentMethodHelper
    ) {
        parent::__construct($context,$layoutFactory,$paymentMethodFactory,$appEmulation,$paymentConfig,$initialConfig);
        $this->IWDPaymentMethodHelper = $IWDPaymentMethodHelper;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
    }
    public function getInfoBlockHtml(\Magento\Payment\Model\InfoInterface $info, $storeId)
    {
        try{
            $paymentCollection = $this->paymentCollectionFactory->create();
            $paymentCollection->addFieldToFilter('entity_id', array("eq" => $info->getEntityId()));
            if($paymentCollection->getSize()){
                $additionalInformation = $paymentCollection->getFirstItem()->getAdditionalInformation();
                if(isset($additionalInformation['iwd_method_title'])){
                    $paymentBlockHtml = '<dl class="payment-method"><dt class="title">'.$additionalInformation['iwd_method_title'].'</dt></dl>';
                    return $paymentBlockHtml;
                }
            }
        }catch (\Exception $e){
            
        }

        return parent::getInfoBlockHtml($info,$storeId);
    }
}
