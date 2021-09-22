<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace IWD\CheckoutConnector\Block\Checkout;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Request\Http;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderRepository;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCheckmoConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayZeroConfigProvider;

class Success extends \Magento\Framework\View\Element\Template
{
    private $request;
    private $checkoutSession;
    private $orderRepository;
    private $checkMoConfigProvider;
    private $zeroConfigProvider;

    public function __construct(
        Context $context,
        Http $request,
        Session $checkoutSession,
        OrderRepository $orderRepository,
        IWDCheckoutOfflinePayCheckmoConfigProvider $checkMoConfigProvider,
        IWDCheckoutOfflinePayZeroConfigProvider $zeroConfigProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->checkMoConfigProvider = $checkMoConfigProvider;
        $this->zeroConfigProvider = $zeroConfigProvider;
    }

    public function getObj(){
        return $this->request->getParams();
    }

    public function getPaymentMethodeDetails(){
        $orderId = $this->checkoutSession->getLastOrderId();
        $order = $this->loadOrderById($orderId);
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $paymentMethodDetails = $this->getPaymentMethodeDetailsByCode($method->getCode());
        return $paymentMethodDetails;
    }

    public function getObjData1(){
        return $this->checkoutSession->getData();
    }

    public function loadOrderById($id){
        return $this->orderRepository->get($id);
    }

    public function getPaymentMethodeDetailsByCode($code){
        switch ($code){
            case 'iwd_checkout_offline_pay_checkmo':
                $configProvider = $this->checkMoConfigProvider;
                break;
            case 'iwd_checkout_offline_pay_zero':
                $configProvider = $this->zeroConfigProvider;
                break;

        }

        if(!isset($configProvider)){
            return [];
        }

        $paymentDetails = [
            'title' => $configProvider->getConfigData('title') ?? '',
            'payable_to' => $configProvider->getConfigData('payable_to') ?? '',
            'mailing_address' => $configProvider->getConfigData('mailing_address') ?? '',
            'instruction' => $configProvider->getConfigData('instruction') ?? '',
            'extra_details' => $configProvider->getConfigData('extra_details') ?? '',
        ];
        foreach ($paymentDetails as $key => $detail){
            if(empty($detail)){
                unset($paymentDetails[$key]);
            }
        }
        return $paymentDetails;
    }

}
