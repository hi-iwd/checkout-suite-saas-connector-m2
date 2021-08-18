<?php
namespace IWD\CheckoutConnector\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class PaymentMethod
 *
 * @package IWD\CheckoutConnector\Model
 */
class PaymentMethod extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'iwd_checkout_pay';
    const ENTITY_ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const PAYMENT_METHOD = 'payment_method';

    protected $_cacheTag = 'iwd_checkout_pay';
    protected $_eventPrefix = 'iwd_checkout_pay';

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('IWD\CheckoutConnector\Model\ResourceModel\PaymentMethod');
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return [];
    }

    /**
     * @return mixed|null
     */
    public function getEntityId(){
        return $this->_getData(self::ENTITY_ID);
    }

    /**
     * @return mixed|null
     */
    public function getOrderId(){
        return $this->_getData(self::ORDER_ID);
    }

    /**
     * @return mixed|null
     */
    public function getPaymentMethod(){
        return $this->_getData(self::PAYMENT_METHOD);
    }

    /**
     * @param int $entityId
     * @return PaymentMethod
     */
    public function setEntityId($entityId){
        return $this->setData(self::ENTITY_ID,$entityId);
    }

    /**
     * @param $orderId
     * @return PaymentMethod
     */
    public function setOrderId($orderId){
        return $this->setData(self::ORDER_ID,$orderId);
    }

    /**
     * @param $title
     * @return PaymentMethod
     */
    public function setPaymentMethod($title){
        return $this->setData(self::PAYMENT_METHOD,$title);
    }
}
