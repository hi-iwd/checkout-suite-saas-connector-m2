<?php
namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\array_iwd;
use IWD\CheckoutConnector\Api\UpdateConfigInterface;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCheckmoConfigProvider;

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
     * UpdateConfig constructor.
     *
     * @param AccessValidator $accessValidator
     * @param IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider
     */
    public function __construct(
        AccessValidator $accessValidator,
        IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider,
        IWDCheckoutOfflinePayCheckmoConfigProvider $IWDCheckoutOfflinePayCheckmoConfigProvider
    ) {
        $this->accessValidator = $accessValidator;
        $this->IWDCheckoutPayConfigProvider = $IWDCheckoutPayConfigProvider;
        $this->IWDCheckoutOfflinePayCheckmoConfigProvider = $IWDCheckoutOfflinePayCheckmoConfigProvider;
    }

    /**
     * @param mixed $access_tokens
     * @param mixed $data
     * @return array_iwd|string
     */
    public function updateConfig($access_tokens, $data)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        if(isset($data['paypal']) && $data['paypal']) {
            $this->IWDCheckoutPayConfigProvider->updateConfig($data['paypal']);
        }

        if(isset($data['apple_pay_minicart']) && $data['apple_pay_minicart']) {
            $this->IWDCheckoutPayConfigProvider->updateConfig(array('apple_pay_minicart' => $data['apple_pay_minicart']));
        }

        if(isset($data['google_pay_minicart']) && $data['google_pay_minicart']) {
            $this->IWDCheckoutPayConfigProvider->updateConfig(array('google_pay_minicart' => $data['google_pay_minicart']));
        }

        if(isset($data['google_pay_info']) && $data['google_pay_info']) {
            $this->IWDCheckoutPayConfigProvider->updateConfig(array('google_pay_info' => $data['google_pay_info']));
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
                }
            }
        }

        return $this->IWDCheckoutPayConfigProvider->getConfigData('google_pay_info');
    }
}
