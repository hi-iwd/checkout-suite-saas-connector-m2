<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace IWD\CheckoutConnector\Block\Info;

class Custom extends \Magento\Payment\Block\Info
{
    private $configProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCustomConfigProvider $configProvider,
        array $data = []
    )
    {
        parent::__construct($context,$data);
        $this->configProvider = $configProvider;
    }

    protected $_template = 'IWD_CheckoutConnector::info/custom.phtml';


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
