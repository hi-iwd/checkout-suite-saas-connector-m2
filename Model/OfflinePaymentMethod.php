<?php
namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\OfflinePaymentMethodInterface;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;

/**
 * Class UpdateConfig
 * @package IWD\CheckoutConnector\Model
 */
class OfflinePaymentMethod implements OfflinePaymentMethodInterface
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
     * @var Config
     */
    private $shipconfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var OrderStatusCollection
     */
    private $orderStatusCollection;

    /**
     * UpdateConfig constructor.
     *
     * @param AccessValidator $accessValidator
     * @param IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider
     */
    public function __construct(
        AccessValidator $accessValidator,
        IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider,
        ScopeConfigInterface $scopeConfig,
        Config $shipconfig,
        OrderStatusCollection $orderStatusCollection
    ) {
        $this->accessValidator = $accessValidator;
        $this->IWDCheckoutPayConfigProvider = $IWDCheckoutPayConfigProvider;
        $this->shipconfig = $shipconfig;
        $this->scopeConfig = $scopeConfig;
        $this->orderStatusCollection = $orderStatusCollection;
    }

    /**
     * @param mixed $access_tokens
     */
    public function getShippingMethods($access_tokens)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        try{
            $activeCarriers = $this->shipconfig->getActiveCarriers();
            foreach($activeCarriers as $carrierCode => $carrierModel) {
                if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                    foreach ($carrierMethods as $methodCode => $method) {
                        $code = $carrierCode . '_' . $methodCode;
                        $options[] = array('value' => $code, 'label' => $method);
                    }
                    $carrierTitle = $this->scopeConfig
                        ->getValue('carriers/'.$carrierCode.'/title');
                }

                $methods[] = array('value' => $options, 'label' => $carrierTitle);
                unset($options);
            }
        }catch (\Exception $e){
            $methods = array();
        }

        return $methods;
    }

    /**
     * @param $access_tokens
     * @return array|string
     */
    public function getOrderStatus($access_tokens)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        try{
            $orderStatus = $this->getAllOrderStatus();
        }catch (\Exception $e){
            $orderStatus = array();
        }

        return $orderStatus;
    }

    public function getAllOrderStatus(){
        return $this->orderStatusCollection->toOptionArray();
}
}
