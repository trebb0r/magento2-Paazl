<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

namespace Paazl\Shipping\Test\Integration;
use Magento\TestFramework\Helper\Bootstrap;
use \Magento\Quote\Model\QuoteTest;

class QuoteExtensionAttributesTest extends QuoteTest
{

    /**
     * Test Getting Quote Address Extension Attributes
     */
    public function testGetQuoteAddressExtensionAttributes()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Quote\Api\CartRepositoryInterface $cartRepository */
        $cartRepository = $objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
        $searchCriteria = $objectManager->create(\Magento\Framework\Api\SearchCriteria::class);
        $cartSearchResult = $cartRepository->getList($searchCriteria);
        foreach($cartSearchResult->getItems() as $quote) {
            $billingAddress = $quote->getBillingAddress();
            $this->assertTrue(true, $billingAddress->getExtensionAttributes()->getStreetName());
            $this->assertTrue(true, $billingAddress->getExtensionAttributes()->getHouseNumber());
            $this->assertTrue(true, $billingAddress->getExtensionAttributes()->getHouseNumberAddition());
        }

    }
}