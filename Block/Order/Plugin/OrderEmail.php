<?php

namespace IWD\CheckoutConnector\Block\Order\Plugin;

use Magento\Sales\Block\Items\AbstractItems;

class OrderEmail
{
    /**
     * @param AbstractItems $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(AbstractItems $subject, string $result): string
    {
        if ($attributesBlock = $subject->getChildBlock('iwd_order_additional_fields')) {
            $result .= $attributesBlock->toHtml();
        }

        return $result;
    }
}
