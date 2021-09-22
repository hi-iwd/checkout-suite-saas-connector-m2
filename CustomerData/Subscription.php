<?php
declare(strict_types=1);

namespace IWD\CheckoutConnector\CustomerData;

use IWD\CheckoutConnector\Api\Data\SubscriptionInterface;
use IWD\CheckoutConnector\Api\SubscriptionRepositoryInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use IWD\CheckoutConnector\Helper\Data;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;

class Subscription implements SectionSourceInterface
{

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var IWDCheckoutPayConfigProvider
     */
    private $configProvider;

    public function __construct(
        SubscriptionRepositoryInterface $subscriptionRepository,
        SearchCriteriaBuilder           $searchCriteriaBuilder,
        Data $helper,
        IWDCheckoutPayConfigProvider $configProvider
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->helper = $helper;
        $this->configProvider = $configProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData(): array
    {
        $subscriptionData = [];
        if ($this->helper->isSubscription()) {
            $this->searchCriteriaBuilder->addFilter(SubscriptionInterface::ACTIVE, 1);
            $this->searchCriteriaBuilder->setPageSize(1000);

            $searchCriteria = $this->searchCriteriaBuilder->create();
            $subscriptionItems = $this->subscriptionRepository->getList($searchCriteria)->getItems();
            if ($subscriptionItems) {
                $bnCode = $this->configProvider->getConfigData('bn_code');
                foreach ($subscriptionItems as $k => $item) {
                    $subscriptionData[$k]['merchant_id'] = $item->getMerchantId();
                    $subscriptionData[$k]['client_id'] = $item->getClientId();
                    $subscriptionData[$k]['sku'] = $item->getSku();
                    $subscriptionData[$k]['plan_id'] = $item->getPlanId();
                    $subscriptionData[$k]['quantity_supported'] = $item->getQuantitySupported();
                    $subscriptionData[$k]['partner_attribution_id'] = $bnCode;

                }
            }
        }
        return ['items' => $subscriptionData];
    }
}
