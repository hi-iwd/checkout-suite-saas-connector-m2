<?php

namespace IWD\CheckoutConnector\Model\Api;

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;

/**
 * Class FormatData
 *
 * @package IWD\CheckoutConnector\Model\Api
 */
class FormatData
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * FormatData constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param null $data
     * @return array|null
     */
    public function format($data = null)
    {
        if ($data == null) {
            return null;
        }

        $formatData = [];
        $email = isset($data['email']) ? $data['email'] : null;
        $shipBillToDiffAddress = $data['ship_bill_to_different_address'];

        foreach ($data as $key => $item) {
            if($key === 'billing' || $key === 'shipping') {
                $regionId = isset($item["region_id"]) ? $item["region_id"] : null;

                if(isset($item["state"]) && !$regionId && $regionData = $this->getRegionDataFromNameIfExists($item["state"])) {
                    $regionId = $regionData['region_id'];
                }

                $formatData[$key] = [
                    "region_id"       => $regionId,
                    "region"          => isset($item["state"]) ? $item["state"] : null,
                    "country_id"      => isset($item['country']) ? $item['country'] : null,
                    "street"          => isset($item['address']) ? $item['address'] : null,
                    "postcode"        => isset($item['postcode']) ? $item['postcode'] : null,
                    "city"            => isset($item['city']) ? $item['city'] : null,
                    "firstname"       => isset($item['first_name']) ? $item['first_name'] : null,
                    "lastname"        => isset($item['last_name']) ? $item['last_name'] : null,
                    "telephone"       => isset($item["phone"]) ? $item["phone"] : null,
                    "email"           => $email,
                    "same_as_billing" => $shipBillToDiffAddress ? $shipBillToDiffAddress : 0,
                ];
            }
        }

        return $formatData;
    }

    /**
     * @param $regionName
     * @return mixed
     */
    public function getRegionDataFromNameIfExists($regionName)
    {
        $regionData = $this->collectionFactory->create()
            ->addRegionNameFilter($regionName)
            ->getFirstItem()
            ->toArray();

        if($regionData) {
            return $regionData;
        }
        return null;
    }
}