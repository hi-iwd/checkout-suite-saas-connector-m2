<?php

namespace IWD\CheckoutConnector\Plugin\Block\Adminhtml;

use Magento\Sales\Block\Adminhtml\Order\View\Info;

class SalesOrderViewInfo
{
    public function afterToHtml(Info $subject, $result)
    {
        $addressBlock = $subject->getLayout()->getBlock('dominate_address_additional_fields');

        if ($addressBlock !== false && $subject->getNameInLayout() == 'order_info') {
            $result = $result . $addressBlock->toHtml();
        }

        return $result;
    }
}