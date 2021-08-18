<?php
namespace IWD\CheckoutConnector\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class PaymentMethod
 *
 * @package IWD\CheckoutConnector\Model\ResourceModel
 */
class PaymentMethod extends AbstractDb
{
    /**
     * PaymentMethod constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('iwd_checkout_pay', 'entity_id');
    }
}
