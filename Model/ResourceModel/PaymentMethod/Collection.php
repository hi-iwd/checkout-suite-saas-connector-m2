<?php
namespace IWD\CheckoutConnector\Model\ResourceModel\PaymentMethod;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package IWD\CheckoutConnector\Model\ResourceModel\PaymentMethod
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'iwd_checkout_pay_collection';
    protected $_eventObject = 'checkout_pay_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('IWD\CheckoutConnector\Model\PaymentMethod', 'IWD\CheckoutConnector\Model\ResourceModel\PaymentMethod');
    }
}
