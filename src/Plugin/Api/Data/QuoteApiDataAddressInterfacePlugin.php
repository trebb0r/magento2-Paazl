<?php
 /**
 * @author    H&O <info@h-o.nl>
 * @copyright 2017 Copyright © H&O (http://www.h-o.nl/)
 * @license   H&O Commercial License (http://www.h-o.nl/modules)
 */
namespace Paazl\Shipping\Plugin\Api\Data;

use \Magento\Quote\Api\Data\AddressInterface;
use \Magento\Quote\Api\Data\AddressExtensionFactory;

class QuoteApiDataAddressInterfacePlugin
{

    /**
     * @var AddressExtensionFactory
     */
    private $quoteAddressExtensionFactory;


    /**
     * QuoteApiDataAddressInterfacePlugin constructor.
     *
     * @param AddressExtensionFactory $quoteAddressExtensionFactory
     */
    public function __construct(
        AddressExtensionFactory $quoteAddressExtensionFactory
    ) {
        $this->quoteAddressExtensionFactory = $quoteAddressExtensionFactory;
    }

    public function afterGetExtensionAttributes(
        AddressInterface $address,
        $extension
    ) {
        if ($extension === null) {
            $extension = $this->quoteAddressExtensionFactory->create();
            $address->setExtensionAttributes($extension);
        }
        $extension->setStreetName($address->getStreetName());
        $extension->setHouseNumber($address->getHouseNumber());
        $extension->setHouseNumberAddition($address->getHouseNumberAddition());
        return $extension;
    }


}