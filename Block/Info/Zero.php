<?php

namespace IWD\CheckoutConnector\Block\Info;

class Zero extends \Magento\Payment\Block\Info
{
    private $configProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayZeroConfigProvider $configProvider,
        array $data = []
    )
    {
        parent::__construct($context,$data);
        $this->configProvider = $configProvider;
    }

    protected $_template = 'IWD_CheckoutConnector::info/offline.phtml';

    public function getTitle()
    {
        return $this->configProvider->getConfigData('title');
    }
}
