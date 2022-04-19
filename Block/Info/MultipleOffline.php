<?php

namespace IWD\CheckoutConnector\Block\Info;

class MultipleOffline extends \Magento\Payment\Block\Info
{
    private $configProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflineMultiple $configProvider,
        array $data = []
    )
    {
        parent::__construct($context,$data);
        $this->configProvider = $configProvider;
    }

    protected $_template = 'IWD_CheckoutConnector::info/offline.phtml';

    public function getExtraDetails()
    {
        return $this->configProvider->getConfigData('extra_details');
    }

    public function getInstruction()
    {
        return $this->configProvider->getConfigData('instruction');
    }

    public function getFieldName()
    {
        return $this->configProvider->getConfigData('field_name');
    }

    public function getTitle()
    {
        $code = $this->getInfo()->getOrder()->getPayment()->getAdditionalInformation('iwd_method_code');
        $this->configProvider->setCode($code);

        return $this->configProvider->getConfigData('title');
    }

    public function getPONumber()
    {
        return $this->getInfo()->getOrder()->getPayment()->getPoNumber();
    }
}
