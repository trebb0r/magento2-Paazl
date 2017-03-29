<?php
/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Guapa\Paazl\Model\Plugin\Quote\Cart;

class ShippingMethodConverterPlugin
{
    /** @var \Magento\Quote\Api\Data\ShippingMethodExtensionFactory  */
    protected $shippingMethodExtensionFactory;

    /** @var \Magento\Checkout\Model\Session  */
    protected $checkoutSession;

    /** @var \Magento\Framework\Api\SimpleDataObjectConverter */
    protected $objectConverter;

    /**
     * ShippingMethodConverter constructor.
     * @param \Magento\Quote\Api\Data\ShippingMethodExtensionFactory $shippingMethodExtensionFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Api\SimpleDataObjectConverter $objectConverter
     */
    public function __construct(
        \Magento\Quote\Api\Data\ShippingMethodExtensionFactory $shippingMethodExtensionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Api\SimpleDataObjectConverter $objectConverter
    ) {
        $this->shippingMethodExtensionFactory = $shippingMethodExtensionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->objectConverter = $objectConverter;
    }

    /**
     * @param $subject
     * @param $result
     * @return \Magento\Quote\Model\Cart\ShippingMethod
     */
    public function afterModelToDataObject(\Magento\Quote\Model\Cart\ShippingMethodConverter $subject, $result)
    {
        if ($result->getCarrierCode() == 'paazl') {
            $paazlData = (!is_null($this->checkoutSession->getPaazlData()))
                ? $this->objectConverter->convertStdObjectToArray($this->checkoutSession->getPaazlData())
                : [];

            $data = ['addressRequest' => []];
            if (isset($paazlData['results']['addressRequest'])) {
                foreach ($paazlData['results']['addressRequest'] as $addressResult) {
                    if (isset($addressResult['address'])) $data['addressRequest'][] = [
                        'address' => $addressResult['address'],
                        'identifier' => $addressResult['identifier']
                    ];
                }
            }

            $encodedData = json_encode($data, JSON_UNESCAPED_SLASHES);

            $shippingExtensionAttributes = $result->getExtensionAttributes();
            $shippingMethodExtension = $shippingExtensionAttributes
                ? $shippingExtensionAttributes
                : $this->shippingMethodExtensionFactory->create();

            $shippingMethodExtension->setPaazlData($encodedData);
            $result->setExtensionAttributes($shippingMethodExtension);
        }

        return $result;
    }
}
