<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Plugin\Quote\Cart;

class ShippingMethodConverterPlugin
{
    /** @var \Magento\Quote\Api\Data\ShippingMethodExtensionFactory  */
    protected $shippingMethodExtensionFactory;

    /** @var \Magento\Checkout\Model\Session  */
    protected $checkoutSession;

    /** @var \Magento\Framework\Api\SimpleDataObjectConverter */
    protected $objectConverter;

    /**
     * @var \Paazl\Shipping\Model\Data\Delivery
     */
    protected $delivery;

    /**
     * ShippingMethodConverter constructor.
     * @param \Magento\Quote\Api\Data\ShippingMethodExtensionFactory $shippingMethodExtensionFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Api\SimpleDataObjectConverter $objectConverter
     */
    public function __construct(
        \Magento\Quote\Api\Data\ShippingMethodExtensionFactory $shippingMethodExtensionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Api\SimpleDataObjectConverter $objectConverter,
        \Paazl\Shipping\Model\Data\Delivery $delivery
    ) {
        $this->shippingMethodExtensionFactory = $shippingMethodExtensionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->objectConverter = $objectConverter;
        $this->delivery = $delivery;
    }

    /**
     * @param $subject
     * @param $result
     * @return \Magento\Quote\Model\Cart\ShippingMethod
     */
    public function afterModelToDataObject(\Magento\Quote\Model\Cart\ShippingMethodConverter $subject, $result)
    {
        if ($result->getCarrierCode() == 'paazl' || $result->getCarrierCode() == 'paazlperfect') {
            $paazlData = (!is_null($this->checkoutSession->getPaazlData()))
                ? $this->objectConverter->convertStdObjectToArray($this->checkoutSession->getPaazlData())
                : [];

            $data = ['addressRequest' => [], 'checkoutRequest' => []];
            if (isset($paazlData['results']['addressRequest'])) {
                foreach ($paazlData['results']['addressRequest'] as $addressResult) {
                    if (isset($addressResult['address'])) $data['addressRequest'][] = [
                        'address' => $addressResult['address'],
                        'identifier' => $addressResult['identifier']
                    ];
                }
            }
            if (isset($paazlData['results']['checkoutRequest'])) {
                $data['checkoutRequest'] = $paazlData['results']['checkoutRequest'];
            }

            $encodedData = json_encode($data, JSON_UNESCAPED_SLASHES);

            $shippingExtensionAttributes = $result->getExtensionAttributes();
            $shippingMethodExtension = $shippingExtensionAttributes
                ? $shippingExtensionAttributes
                : $this->shippingMethodExtensionFactory->create();

            $shippingMethodExtension->setPaazlData($encodedData);

            $result->setExtensionAttributes($shippingMethodExtension);
        }

        if ($result->getCarrierCode() == 'paazlperfect') {
            if (isset($paazlData['delivery']) && isset($paazlData['delivery']['servicePoint'])) {
                $delivery = $this->delivery;

                if (isset($paazlData['delivery']['servicePoint']['address'])) {
                    $delivery->setServicePointName($paazlData['delivery']['servicePoint']['name']);
                    $delivery->setServicePointAddress($paazlData['delivery']['servicePoint']['address']);
                    $delivery->setServicePointPostcode($paazlData['delivery']['servicePoint']['postcode']);
                    $delivery->setServicePointCity($paazlData['delivery']['servicePoint']['city']);
                }
                else {
                    $delivery->setData([]);
                }

                $shippingMethodExtension->setDelivery($delivery);
            }
            else {
                $data;
            }

            $result->setExtensionAttributes($shippingMethodExtension);
        }

        return $result;
    }
}
