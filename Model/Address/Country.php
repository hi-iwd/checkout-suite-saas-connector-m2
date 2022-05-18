<?php

namespace IWD\CheckoutConnector\Model\Address;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Directory\Model\TopDestinationCountries;
use Magento\Framework\App\ObjectManager;

/**
 * Class Country
 *
 * @package IWD\CheckoutConnector\Model\Address
 */
class Country
{
    /**
     * @var Collection
     */
    protected $countryCollection;

    /**
     * @var mixed
     */
    private $topDestinationCountries;

    /**
     * Country constructor.
     * @param CollectionFactory $countryCollection
     * @param TopDestinationCountries|null $topDestinationCountries
     */
    public function __construct(
        CollectionFactory $countryCollection,
        TopDestinationCountries $topDestinationCountries = null
    ) {
        $this->countryCollection = $countryCollection;
        $this->topDestinationCountries = $topDestinationCountries ?:
            ObjectManager::getInstance()
                ->get(TopDestinationCountries::class);
    }

    /**
     * @param $quote
     * @return array
     */
    public function getCountry($quote)
    {
        $data = $this->countryCollection->create()->loadByStore($quote->getStoreId())
            ->setForegroundCountries($this->topDestinationCountries->getTopDestinations())
            ->toOptionArray();

        return $this->filterCountryResult($data);
    }

    public function getAllCountry()
    {
        $data = $this->countryCollection->create()->toOptionArray();

        return $this->filterCountryResult($data);

    }

    private function filterCountryResult($data) {
        if (count($data) > 1) {
            array_shift($data);
        }

        return $data;
    }
}
