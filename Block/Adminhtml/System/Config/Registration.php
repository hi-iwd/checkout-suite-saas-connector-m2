<?php

namespace IWD\CheckoutConnector\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Documentation
 * @package IWD\CheckoutConnector\Block\Adminhtml\System\Config
 */
class Registration extends Field
{
    private $userGuideUrl = "https://www.dominate.co/account/create";

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return sprintf(
            "<span style='margin-bottom:-8px; display:block;'><a href='%s' target='_blank'>%s</a></span>",
            $this->userGuideUrl,
            __("Registration")
        );
    }
}
