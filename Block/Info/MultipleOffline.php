<?php

namespace IWD\CheckoutConnector\Block\Info;

use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflineMultiple;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Info;

/**
 * Class MultipleOffline
 *
 * @package IWD\CheckoutConnector\Block\Info
 */
class MultipleOffline extends Info
{
    private $configProvider;
    protected $_template = 'IWD_CheckoutConnector::info/offline.phtml';

    /**
     * MultipleOffline constructor.
     *
     * @param Context $context
     * @param IWDCheckoutOfflineMultiple $configProvider
     * @param array $data
     * @throws LocalizedException
     */
    public function __construct(
        Context $context,
        IWDCheckoutOfflineMultiple $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    /**
     * @return mixed
     */
    public function getExtraDetails()
    {
        return $this->configProvider->getConfigData('extra_details');
    }

    /**
     * @return mixed
     */
    public function getInstruction()
    {
        return $this->configProvider->getConfigData('instruction');
    }

    /**
     * @return mixed
     */
    public function getFieldName()
    {
        return $this->configProvider->getConfigData('field_name');
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getTitle()
    {
        $code = $this->getInfo()->getOrder()->getPayment()->getAdditionalInformation('iwd_method_code');
        $this->configProvider->setCode($code);

        return $this->configProvider->getConfigData('title');
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function getPONumber()
    {
        return $this->getInfo()->getOrder()->getPayment()->getPoNumber();
    }
}
