<?php
namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\UpdateConfigInterface;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCheckmoConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCashOnDeliveryConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayBankTransferConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayPurchaseOrderConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflineMultiple;

/**
 * Class UpdateConfig
 * @package IWD\CheckoutConnector\Model
 */
class UpdateConfig implements UpdateConfigInterface
{
    /**
     * @var AccessValidator
     */
    private $accessValidator;

    /**
     * @var IWDCheckoutPayConfigProvider
     */
    private $IWDCheckoutPayConfigProvider;

    /**
     * @var IWDCheckoutOfflinePayCheckmoConfigProvider
     */
    private $IWDCheckoutOfflinePayCheckmoConfigProvider;

    /**
     * @var IWDCheckoutOfflinePayCashOnDeliveryConfigProvider
     */
    private $IWDCheckoutOfflinePayCashOnDeliveryConfigProvider;

    /**
     * @var IWDCheckoutOfflinePayBankTransferConfigProvider
     */
    private $IWDCheckoutOfflinePayBankTransferConfigProvider;

    /**
     * @var IWDCheckoutOfflinePayPurchaseOrderConfigProvider
     */
    private $IWDCheckoutOfflinePayPurchaseOrderConfigProvider;
    /**
     * @var IWDCheckoutOfflineMultiple
     */
    private $IWDCheckoutOfflineMultiple;

    /**
     * UpdateConfig constructor.
     *
     * @param AccessValidator $accessValidator
     * @param IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider
     */
    public function __construct(
        AccessValidator $accessValidator,
        IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider,
        IWDCheckoutOfflinePayCheckmoConfigProvider $IWDCheckoutOfflinePayCheckmoConfigProvider,
        IWDCheckoutOfflinePayCashOnDeliveryConfigProvider $IWDCheckoutOfflinePayCashOnDeliveryConfigProvider,
        IWDCheckoutOfflinePayBankTransferConfigProvider $IWDCheckoutOfflinePayBankTransferConfigProvider,
        IWDCheckoutOfflinePayPurchaseOrderConfigProvider $IWDCheckoutOfflinePayPurchaseOrderConfigProvider,
        IWDCheckoutOfflineMultiple $IWDCheckoutOfflineMultiple
    ) {
        $this->accessValidator = $accessValidator;
        $this->IWDCheckoutPayConfigProvider = $IWDCheckoutPayConfigProvider;
        $this->IWDCheckoutOfflinePayCheckmoConfigProvider = $IWDCheckoutOfflinePayCheckmoConfigProvider;
        $this->IWDCheckoutOfflinePayCashOnDeliveryConfigProvider = $IWDCheckoutOfflinePayCashOnDeliveryConfigProvider;
        $this->IWDCheckoutOfflinePayBankTransferConfigProvider = $IWDCheckoutOfflinePayBankTransferConfigProvider;
        $this->IWDCheckoutOfflinePayPurchaseOrderConfigProvider = $IWDCheckoutOfflinePayPurchaseOrderConfigProvider;
        $this->IWDCheckoutOfflineMultiple = $IWDCheckoutOfflineMultiple;
    }

    /**
     * @param mixed $access_tokens
     * @param mixed $data
     * @return mixed[]|string
     */
    public function updateConfig($access_tokens, $data)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        if(isset($data['paypal']) && $data['paypal']) {
            $this->IWDCheckoutPayConfigProvider->updateConfig($data['paypal']);
        }

        if(isset($data['offline_payments']) && $data['offline_payments']) {
            foreach ($data['offline_payments'] as $gateway_type => $gateway_settings){
                switch ($gateway_type){
                    case 'check_or_money_order':
                        foreach ($gateway_settings as $k => $v){
                            if(!empty($v)){
                                $this->IWDCheckoutOfflinePayCheckmoConfigProvider->updateConfig(array($k => $v));
                            }
                        }
                        break;
                    case 'cash_on_delivery':
                        foreach ($gateway_settings as $k => $v){
                            if(!empty($v)){
                                $this->IWDCheckoutOfflinePayCashOnDeliveryConfigProvider->updateConfig(array($k => $v));
                            }
                        }
                        break;
                    case 'banktransfer':
                        foreach ($gateway_settings as $k => $v){
                            if(!empty($v)){
                                $this->IWDCheckoutOfflinePayBankTransferConfigProvider->updateConfig(array($k => $v));
                            }
                        }
                        break;
                    case 'purchaseorder':
                        foreach ($gateway_settings as $k => $v){
                            if(!empty($v)){
                                $this->IWDCheckoutOfflinePayPurchaseOrderConfigProvider->updateConfig(array($k => $v));
                            }
                        }
                        break;
                    default:
                        foreach ($gateway_settings as $k => $v){
                            if(!empty($v)){
                                $this->IWDCheckoutOfflineMultiple->updateConfig(array($k => $v) , $gateway_type);
                            }
                        }
                }
            }
        }

        return 'Success!';
    }
}
