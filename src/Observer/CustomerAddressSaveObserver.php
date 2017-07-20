<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CustomerAddressSaveObserver implements ObserverInterface
{
    /**
     * @var \Paazl\Shipping\Helper\Utility\Address
     */
    protected $addressHelper;

    /**
     * CustomerAddressSaveObserver constructor.
     * @param \Paazl\Shipping\Helper\Utility\Address $addressHelper
     */
    public function __construct(
        \Paazl\Shipping\Helper\Utility\Address $addressHelper
    )
    {
        $this->addressHelper = $addressHelper;
    }


    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Customer\Model\Address $address */
        $address = $observer->getEvent()->getCustomerAddress();
        $houseNumberFull = '';

        // convert old address to new format
        $streetParts = $this->addressHelper->getMultiLineStreetParts($address->getStreet());
        if (!$streetParts['house_number']) {
            // Get street, house number, etc from line 1
            $streetParts = $this->addressHelper->getStreetParts($address->getStreet());
        }
        if ($address->getStreetName() != '') {
            $streetParts['street'] = $address->getStreetName();
        }
        if ($address->getHouseNumber() != '') {
            $streetParts['house_number'] = $address->getHouseNumber();
            $houseNumberFull = $streetParts['house_number'];
        }
        if ($address->getHouseNumberAddition() != '') {
            $streetParts['addition'] = $address->getHouseNumberAddition();
            $houseNumberFull .= ' ' . $streetParts['addition'];
        }

        // @todo: check if already has values for house_number, etc
        $address->setStreetName($streetParts['street']);
        $address->setHouseNumber($streetParts['house_number']);
        $address->setHouseNumberAddition($streetParts['addition']);



        $address->setStreet($streetParts['street'] . " " . $houseNumberFull);
    }
}
