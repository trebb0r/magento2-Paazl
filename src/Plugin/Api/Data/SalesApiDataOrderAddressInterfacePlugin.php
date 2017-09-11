<?php
 /**
 * @author    H&O <info@h-o.nl>
 * @copyright 2017 Copyright © H&O (http://www.h-o.nl/)
 * @license   H&O Commercial License (http://www.h-o.nl/modules)
 */
namespace Paazl\Shipping\Plugin\Api\Data;

use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderAddressExtensionFactory;

class SalesApiDataOrderAddressInterfacePlugin
{

    /**
     * @var OrderAddressExtensionFactory
     */
    private $orderAddressExtensionFactory;


    /**
     * SalesOrderApiDataOrderAddressInterfacePlugin constructor.
     *
     * @param OrderAddressExtensionFactory $orderAddressExtensionFactory
     */
    public function __construct(
        OrderAddressExtensionFactory $orderAddressExtensionFactory
    ) {
        $this->orderAddressExtensionFactory = $orderAddressExtensionFactory;
    }

    public function afterGetExtensionAttributes(
        OrderAddressInterface $orderAddress,
        $extension
    ) {
        if ($extension === null) {
            $extension = $this->orderAddressExtensionFactory->create();
            $orderAddress->setExtensionAttributes($extension);
        }
        $extension->setStreetName($orderAddress->getStreetName());
        $extension->setHouseNumber($orderAddress->getHouseNumber());
        $extension->setHouseNumberAddition($orderAddress->getHouseNumberAddition());
        return $extension;
    }
}