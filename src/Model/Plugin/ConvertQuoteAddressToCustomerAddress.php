<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Plugin;
use Paazl\Shipping\Helper\Utility\Address;

class ConvertQuoteAddressToCustomerAddress
{
    /**
     * @var \Paazl\Shipping\Helper\Data
     */
    private $customerData;

    private $addressHelper;


    /**
     * ConvertQuoteAddressToCustomerAddress constructor.
     *
     * @param \Paazl\Shipping\Helper\Data $customerData
     * @param Address                     $addressHelper
     */
    public function __construct(
        \Paazl\Shipping\Helper\Data $customerData,
        Address $addressHelper
    ) {
        $this->customerData = $customerData;
        $this->addressHelper = $addressHelper;
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $quoteAddress
     * @param \Magento\Customer\Api\Data\AddressInterface $customerAddress
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function afterExportCustomerAddress(
        \Magento\Quote\Api\Data\AddressInterface $quoteAddress,
        \Magento\Customer\Api\Data\AddressInterface $customerAddress
    ) {
        $attributes = $this->customerData->getCustomerAddressUserDefinedAttributeCodes();
        foreach ($attributes as $attribute) {
            $customerAddress->setCustomAttribute($attribute, $quoteAddress->getData($attribute));
        }
        return $customerAddress;
    }


    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     */
    public function beforeBeforeSave(\Magento\Quote\Model\Quote\Address $address)
    {
        $convertedStreet = $this->addressHelper->getMultiLineStreetParts($address->getStreet());
        if (!$convertedStreet['house_number']) {
            $convertedStreet = $this->addressHelper->getStreetParts($address->getStreet());
        }
        $address->setStreetName($convertedStreet['street']);
        $address->setHouseNumber($convertedStreet['house_number']);
        $address->setHouseNumberAddition($convertedStreet['addition']);
    }


}
