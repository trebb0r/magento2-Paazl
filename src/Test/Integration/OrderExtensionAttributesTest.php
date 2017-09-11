<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

namespace Paazl\Shipping\Test\Integration;
use Magento\TestFramework\Helper\Bootstrap;

class OrderExtensionAttributesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test Getting Order Address Extension Attributes
     */
    public function testGetOrderAddressExtensionAttributes()
    {
        /** @var \Magento\Sales\Api\OrderAddressRepositoryInterface $orderAddressRepository */
        $orderAddressRepository = Bootstrap::getObjectManager()->create(\Magento\Sales\Api\OrderAddressRepositoryInterface::class);
        /** @var \Magento\Sales\Model\Order\Address $orderAddress */
        $orderAddress = $orderAddressRepository->get(1);
        $this->assertTrue(true, $orderAddress->getExtensionAttributes()->getStreetName());
        $this->assertTrue(true, $orderAddress->getExtensionAttributes()->getHouseNumber());
        $this->assertTrue(true, $orderAddress->getExtensionAttributes()->getHouseNumberAddition());
    }


    /**
     * Test Saving Order Address Extension Attributes
     */
    public function testSaveOrderAddressExtensionAttributes()
    {
        /** @var \Magento\Sales\Api\OrderAddressRepositoryInterface $orderAddressRepository */
        $orderAddressRepository = Bootstrap::getObjectManager()->create(\Magento\Sales\Api\OrderAddressRepositoryInterface::class);
        /** @var \Magento\Sales\Model\Order\Address $orderAddress */
        $orderAddress = $orderAddressRepository->get(1);
        $orderAddress->setHouseNumberAddition('G');
        $orderAddressRepository->save($orderAddress);
        $this->assertEquals('Honey Bluff Parkway', $orderAddress->getExtensionAttributes()->getStreetName());
        $this->assertEquals(6146, $orderAddress->getExtensionAttributes()->getHouseNumber());
        $this->assertEquals('G', $orderAddress->getExtensionAttributes()->getHouseNumberAddition());
    }
}