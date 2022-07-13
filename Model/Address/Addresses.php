<?php

namespace IWD\CheckoutConnector\Model\Address;

use Magento\Customer\Model\AddressFactory;

/**
 * Class Addresses
 * @package IWD\CheckoutConnector\Model\Address
 */
class Addresses
{
    /**
     * @var AddressFactory
     */
    private $addressRepository;

    /**
     * Addresses constructor.
     *
     * @param AddressFactory $addressRepository
     */
    public function __construct(
        AddressFactory $addressRepository
    ) {
        $this->addressRepository = $addressRepository;
    }

    /**
     * @param $quote
     * @return array
     */
    public function getCustomerAddresses($quote)
    {
        $defaultAddress = [];
        $customer = $quote->getCustomer();
        $shippingAddressId = $customer->getDefaultShipping();
        $billingAddressId = $customer->getDefaultBilling();

        if ($shippingAddressId) {
            $defaultAddress['shipping'] = $this->addressRepository->create()->load($shippingAddressId)->getData();
        }

        if ($billingAddressId) {
            $defaultAddress['billing'] = $this->addressRepository->create()->load($billingAddressId)->getData();
        }

        $quoteAddress = $this->getAddresses($quote);

        return $this->prepareForCustomer($quoteAddress, $defaultAddress);
    }

    /**
     * @param $quote
     * @return array
     */
    public function getSavedCustomerAddresses($quote)
    {
        $resultAddress = [];

        $customer = $quote->getCustomer();

        if ($customer->getAddresses()) {
            foreach ($customer->getAddresses() as $address) {
                $resultAddress[] = [
                    'value'      => $address->getId(),
                    'first_name' => $address->getFirstname(),
                    'last_name'  => $address->getLastname(),
                    'address'    => $address->getStreet()[0],
                    'city'       => $address->getCity(),
                    'country'    => $address->getCountryId(),
                    'state'      => $address->getRegion()->getRegion(),
                    'region_id'  => $address->getRegion()->getRegionId(),
                    'postcode'   => $address->getPostcode(),
                    'phone'      => $address->getTelephone(),
                ];
            }
        }

        return $resultAddress;
    }

    /**
     * @param $savedAddress
     * @param null $address
     * @return array
     */
    public function prepareForCustomer($savedAddress, $address = null)
    {
        $data = [];
        $address = $address ? $address : $savedAddress;

        foreach ($address as $key => $item) {
            $data[$key] = [
                'firstname'  => $this->getSavedAddressValue('firstname', $savedAddress[$key], $item),
                'lastname'   => $this->getSavedAddressValue('lastname', $savedAddress[$key], $item),
                'street'     => $this->getSavedAddressValue('street', $savedAddress[$key], $item),
                'country_id' => $this->getSavedAddressValue('country_id', $savedAddress[$key], $item),
                'region'     => $this->getSavedAddressValue('region', $savedAddress[$key], $item),
                'region_id'  => $this->getSavedAddressValue('region_id', $savedAddress[$key], $item),
                'city'       => $this->getSavedAddressValue('city', $savedAddress[$key], $item),
                'postcode'   => $this->getSavedAddressValue('postcode', $savedAddress[$key], $item),
                'telephone'  => $this->getSavedAddressValue('telephone', $savedAddress[$key], $item),
                'company'    => $this->getSavedAddressValue('company', $savedAddress[$key], $item),
            ];
        }

        return $data;
    }

    /**
     * @param $key
     * @param $savedAddress
     * @param $address
     * @return mixed
     */
	protected function getSavedAddressValue($key, $savedAddress, $address)
	{
		if (isset($savedAddress[$key]) && $savedAddress[$key]) {
			return $savedAddress[$key];
		}
		if (isset($address[$key]) && $address[$key]) {
			return $address[$key];
		}
		return '';
	}

    /**
     * @param $quote
     * @return array
     */
    public function formatAddress($quote)
    {
        $address = [];
        $data = $this->getAddresses($quote);

        foreach ($data as $key => $item) {
            $address[$key] = [
                'email'       => $item->getEmail(),
                'first_name'  => $item->getFirstName(),
                'last_name'   => $item->getLastName(),
                'address'     => $item->getStreetFull(),
                'country'     => $item->getCountryId(),
                'state'       => $item->getRegion(),
                "region_id"   => $item->getRegionId(),
                "region_code" => $item->getRegionCode(),
                'city'        => $item->getCity(),
                'postcode'    => $item->getPostcode(),
                'phone'       => $item->getTelephone()
            ];
        }

        $address['ship_bill_to_different_address'] = $data['billing']['same_as_billing'];

        return $address;
    }

    /**
     * @param $quote
     * @return mixed
     */
    public function getAddresses($quote)
    {
        $result['billing']  = $quote->getBillingAddress();
        $result['shipping'] = $quote->getShippingAddress();

        return $result;
    }

    /**
     * @param $quote
     * @return bool
     */
    public function isLoggedIn($quote)
    {
        $data = $quote->getCustomer()->getId();

        return (bool)$data;
    }
}