<?php

namespace IWD\CheckoutConnector\Block\Info;

class Checkmo extends \Magento\Payment\Block\Info
{
    private $configProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCheckmoConfigProvider $configProvider,
        array $data = []
    )
    {
        parent::__construct($context,$data);
        $this->configProvider = $configProvider;
    }

    protected $_template = 'IWD_CheckoutConnector::info/offline.phtml';

    public function getPayableTo()
    {
        return $this->configProvider->getConfigData('payable_to');
    }

    public function getMailingAddress()
    {
        return $this->configProvider->getConfigData('mailing_address');
    }

    public function getExtraDetails()
    {
        return $this->configProvider->getConfigData('extra_details');
    }

    public function getInstruction()
    {
        return $this->configProvider->getConfigData('instruction');
    }

    public function getTitle()
    {
        return $this->configProvider->getConfigData('title');
    }
}
