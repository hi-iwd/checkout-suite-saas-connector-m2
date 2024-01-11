<?php

namespace IWD\CheckoutConnector\Block\Adminhtml\Order;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class AdditionalFields extends \Magento\Backend\Block\Template
{

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context  $context,
        Registry $registry,
        array    $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        if ($this->isInvoiceViewPage()) return $this->_coreRegistry->registry('current_invoice')->getOrder();
        if ($this->isRefundViewPage()) return $this->_coreRegistry->registry('current_creditmemo')->getOrder();
        if ($this->isShipmentViewPage()) return $this->_coreRegistry->registry('current_shipment')->getOrder();
        if ($this->isEmail()) return $this->getParentBlock()->getOrder();

        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @return mixed
     */
    public function getAdditionalFields()
    {
        return json_decode($this->getOrder()->getDominateAdditionalFields() ?? '');
    }

    /**
     * @return mixed|null
     */
    public function getInvoice()
    {
        return $this->_coreRegistry->registry('current_invoice');
    }

    /**
     * @return mixed
     */
    public function getPersonalDetails()
    {
        return $this->getAdditionalFields()->personal_details ?? null;
    }

    /**
     * @return mixed
     */
    public function getShippingInformation()
    {
        return $this->getAdditionalFields()->shipping_information ?? null;
    }

    /**
     * @return mixed
     */
    public function getBillingInformation()
    {
        return $this->getAdditionalFields()->billing_information ?? null;
    }

    /**
     * @return mixed
     */
    public function getOrderSummary()
    {
        return $this->getAdditionalFields()->order_summary;
    }

    /**
     * @return mixed
     */
    public function getShippingMethods()
    {
        return $this->getAdditionalFields()->shipping_methods ?? null;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethods()
    {
        return $this->getAdditionalFields()->payment_methods ?? null;
    }

    /**
     * @return bool
     */
    public function isRefundViewPage()
    {
        return $this->getOrderInfoArea() === 'creditmemo';
    }

    /**
     * @return bool
     */
    public function isShipmentViewPage()
    {
        return $this->getOrderInfoArea() === 'shipment';
    }

    /**
     * @return bool
     */
    public function isInvoiceViewPage()
    {
        return $this->getOrderInfoArea() === 'invoice';
    }

    /**
     * @return bool
     */
    public function isEmail()
    {
        return $this->getOrderInfoArea() === 'email';
    }

    /**
     * @param $option
     * @return string
     */
    public function parseOption($option)
    {
        return $option == 'on' ? 'checked' : '';
    }
}
