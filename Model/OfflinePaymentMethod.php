<?php

namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\OfflinePaymentMethodInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;
use Magento\Customer\Model\ResourceModel\Group\Collection as CustomerGroup;

/**
 * Class OfflinePaymentMethod
 * @package IWD\CheckoutConnector\Model
 */
class OfflinePaymentMethod implements OfflinePaymentMethodInterface
{
    /**
     * @var AccessValidator
     */
    private $accessValidator;

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
	 * @var CustomerGroup
	 */
	private $customerGroup;

    /**
     * @param AccessValidator $accessValidator
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $shipconfig
     * @param OrderStatusCollection $orderStatusCollection
     * @param CustomerGroup $customerGroup
     */
    public function __construct(
        AccessValidator              $accessValidator,
        ScopeConfigInterface         $scopeConfig,
        Config                       $shipconfig,
        OrderStatusCollection        $orderStatusCollection,
        CustomerGroup                $customerGroup
    )
    {
        $this->accessValidator = $accessValidator;
        $this->shipconfig = $shipconfig;
        $this->scopeConfig = $scopeConfig;
        $this->orderStatusCollection = $orderStatusCollection;
        $this->customerGroup = $customerGroup;
    }

    /**
     * @param mixed $access_tokens
     * @return mixed[]|string
     */
    public function getShippingMethods($access_tokens)
    {
        if (!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        try {
            $activeCarriers = $this->shipconfig->getActiveCarriers();
            foreach ($activeCarriers as $carrierCode => $carrierModel) {
                if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                    foreach ($carrierMethods as $methodCode => $method) {
                        $code = $carrierCode . '_' . $methodCode;
                        $options[] = array('value' => $code, 'label' => $method);
                    }
                    $carrierTitle = $this->scopeConfig
                        ->getValue('carriers/' . $carrierCode . '/title');
                }

                $methods[] = array('value' => $options, 'label' => $carrierTitle);
                unset($options);
            }
        } catch (\Exception $e) {
            $methods = array();
        }

        return $methods;
    }

    /**
     * @param $access_tokens
     * @return mixed[]|string
     */
    public function getOrderStatus($access_tokens)
    {
        if (!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        try {
            $orderStatus = $this->getAllOrderStatus();
        } catch (\Exception $e) {
            $orderStatus = array();
        }

        return $orderStatus;
    }

    /**
     * @return array
     */
    public function getAllOrderStatus()
    {
        return $this->orderStatusCollection->toOptionArray();
    }

    /**
     * @param $access_tokens
     * @return mixed[]|string|void
     */
    public function getGroups($access_tokens)
    {
        if (!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        return $this->customerGroup->toOptionArray();
    }
}
