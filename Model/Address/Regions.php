<?php

namespace IWD\CheckoutConnector\Model\Address;

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;

/**
 * Class Regions
 *
 * @package IWD\CheckoutConnector\Model\Address
 */
class Regions
{
    /**
     * @var CollectionFactory
     */
    protected $regionCollection;

    /**
     * Regions constructor.
     *
     * @param CollectionFactory $regionCollection
     */
    public function __construct(
        CollectionFactory $regionCollection
    ) {
        $this->regionCollection = $regionCollection;
    }

    /**
     * @return array
     */
    public function getRegions()
    {
        return $this->regionCollection->create()->addAllowedCountriesFilter()->toOptionArray();
    }

    public function getAllRegions()
    {
        return $this->regionCollection->create()->toOptionArray();
    }
}
