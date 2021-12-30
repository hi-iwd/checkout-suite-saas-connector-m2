<?php

namespace IWD\CheckoutConnector\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Vault\Model\CreditCardTokenFactory;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\PaymentTokenManagement as PaymentTokenLink;

class CreditCard extends AbstractHelper
{
    public $cardTokenFactory;

    public $paymentTokenManagement;

    public $paymentTokenLink;

    public function __construct(
        CreditCardTokenFactory $cardTokenFactory,
        PaymentTokenRepositoryInterface $paymentTokenManagement,
        PaymentTokenLink $paymentTokenLink
    ) {
        $this->cardTokenFactory = $cardTokenFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->paymentTokenLink = $paymentTokenLink;
    }

    public function generateHash($creditCardToken)
    {
        return sha1($creditCardToken['saved_credit_card']['token']);
    }

    public function convertAuthCreditCardData($authCreditCardData){
        return explode('#',$authCreditCardData['saved_credit_card']['third_party_token']);
    }

    public function saveCreditCard($order,$data){
        switch ($data['saved_credit_card']['payment_method_code']){
            case 'iwd_authcim':
                return  $this->saveToAuthNetCIM($order,$data);
                break;
            case 'braintree':
                return $this->saveToBraintree($order,$data);
                break;
        }
    }

    public function saveToAuthNetCIM($order,$data){
        try{
            if (interface_exists(\IWD\AuthCIM\Api\Data\CardInterface::class)) {
                $objectManager = ObjectManager::getInstance();
                $iwdCreditCard = $objectManager->create(\IWD\AuthCIM\Api\Data\CardInterface::class);
                $authCreditCard = $this->convertAuthCreditCardData($data);
                $iwdCreditCard->setHash($this->generateHash($data));

                if(!is_null($order->getCustomerId())){$iwdCreditCard->setCustomerId($order->getCustomerId());}
                else{$iwdCreditCard->setCustomerId(null);}

                $iwdCreditCard->setCustomerEmail($order->getCustomerEmail());
                $iwdCreditCard->setCustomerProfileId($authCreditCard[0]);
                $iwdCreditCard->setPaymentId($authCreditCard[1]);
                $iwdCreditCard->setMethodName('iwd_authcim');
                $iwdCreditCard->setIsActive(1);
                $iwdCreditCard->save();
            }else{
                return array();
            }
        }catch (\Exception $e){
            return array();
        }

        return array(
            'id' => $iwdCreditCard->getEntityId(),
            'method' => $iwdCreditCard->getMethodName(),
            'cc_id' => $iwdCreditCard->getHash(),
        );
    }

    public function saveToBraintree($order,$data){
        try{
            $paymentToken = $this->cardTokenFactory->create();
            $tokenDetails = $this->getTokenDetails($order);
            $paymentToken->setExpiresAt($this->getLastDayOfMonth($data['saved_credit_card']['card']));
            $paymentToken->setGatewayToken($data['saved_credit_card']['third_party_token']);
            $paymentToken->setTokenDetails($tokenDetails);
            $paymentToken->setIsActive(true);
            $paymentToken->setIsVisible(false);
            $paymentToken->setPaymentMethodCode('braintree');

            if(!is_null($order->getCustomerId())){$paymentToken->setCustomerId($order->getCustomerId());}
            else{$paymentToken->setCustomerId(null);}

            $paymentToken->setPublicHash($this->generateHash($data));
            $iwdCreditCard = $this->paymentTokenManagement->save($paymentToken);

            $this->paymentTokenLink->addLinkToOrderPayment($iwdCreditCard->getEntityId(),$order->getPayment()->getEntityId());
        }catch (\Exception $e){
            return array();
        }

        return array(
            'id' => $iwdCreditCard->getEntityId(),
            'method' => $iwdCreditCard->getPaymentMethodCode(),
            'cc_id' => $iwdCreditCard->getPublicHash(),
        );
    }

    public function getLastDayOfMonth($card){
        $date = $card['year'] . '-' . $card['month'] . '-01';
        return date("Y-m-t", strtotime($date));
    }

    public function getTokenDetails($order){
        try{
            $details = array(
                'order_id' => $order->getId(),
                'payment_id' => $order->getPayment()->getEntityId(),
            );
            return json_encode($details);
        }catch (\Exception $e){
            return NULL;
        }
    }
}
