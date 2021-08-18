<?php

namespace IWD\CheckoutConnector\Model\ResourceModel\Order\Grid;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OriginalCollection;

/**
 * Order grid extended collection
 */
class Collection extends OriginalCollection
{
    /**
     * @return Collection|void
     */
    public function _initSelect()
    {
        $this->addFilterToMap('main_table.iwd_checkout_pay', 'iwd_checkout_pay.payment_method');

        parent::_initSelect();
    }
}
