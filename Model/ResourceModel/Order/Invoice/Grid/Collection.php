<?php

namespace IWD\CheckoutConnector\Model\ResourceModel\Order\Invoice\Grid;

/**
 * Class Collection
 *
 * @package IWD\CheckoutConnector\Model\ResourceModel\Order\Invoice\Grid
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Invoice\Orders\Grid\Collection
{

    public function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();
    }

    /**
     * @return Collection|void
     */
    public function _initSelect()
    {
        $this->addFilterToMap('iwd_checkout_pay', 'iwd_checkout_pay.payment_method');

        parent::_initSelect();
    }
}
