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

    /** @var \Magento\Framework\Api\SimpleDataObjectConverter */
    protected $objectConverter;

    /**
     * @var \Paazl\Shipping\Model\Data\DeliveryFactory
     */
    protected $deliveryFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezoneInterface;

    /**
     * @var \Paazl\Shipping\Model\PaazlManagement
     */
    protected $_paazlManagement;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface
     */
    protected $dateTimeFormatter;

    /**
     * ShippingMethodConverter constructor.
     * @param \Magento\Quote\Api\Data\ShippingMethodExtensionFactory $shippingMethodExtensionFactory
     * @param \Magento\Framework\Api\SimpleDataObjectConverter $objectConverter
     * @param \Paazl\Shipping\Model\Data\DeliveryFactory $deliveryFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     * @param \Paazl\Shipping\Model\PaazlManagement $_paazlManagement
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter
     */
    public function __construct(
        \Magento\Quote\Api\Data\ShippingMethodExtensionFactory $shippingMethodExtensionFactory,
        \Magento\Framework\Api\SimpleDataObjectConverter $objectConverter,
        \Paazl\Shipping\Model\Data\DeliveryFactory $deliveryFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Paazl\Shipping\Model\PaazlManagement $_paazlManagement,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface $dateTimeFormatter
    ) {
        $this->shippingMethodExtensionFactory = $shippingMethodExtensionFactory;
        $this->objectConverter = $objectConverter;
        $this->deliveryFactory = $deliveryFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->_paazlManagement = $_paazlManagement;
        $this->registry = $registry;
        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    /**
     * @param $subject
     * @param $result
     * @return \Magento\Quote\Model\Cart\ShippingMethod
     */
    public function afterModelToDataObject(\Magento\Quote\Model\Cart\ShippingMethodConverter $subject, $result)
    {
        if ($result->getCarrierCode() == 'paazl' || $result->getCarrierCode() == 'paazlp') {
            $paazlData = $this->registry->registry('paazlData');

            $paazlData = (!is_null($paazlData))
                ? $this->objectConverter->convertStdObjectToArray($paazlData)
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

        if ($result->getCarrierCode() == 'paazl') {
            $delivery = $this->deliveryFactory->create();
            $shippingOptions = $this->_paazlManagement->getShippingOptions();

            foreach($shippingOptions as $shippingOption) {
                if ($shippingOption['type'] == $result->getMethodCode() && isset($shippingOption['deliveryDates'])) {
                    $firstShippingOption = $shippingOption['deliveryDates']['deliveryDateOption'][0];
                    $dateTime = $firstShippingOption['deliveryDate'];
                    $dateAsTimeZone = $this->timezoneInterface
                        ->date(new \DateTime($dateTime))
                        ->format('l j F');
                    $dateAsTimeZone = $this->dateTimeFormatter->formatObject($this->timezoneInterface->date(new \DateTime($dateTime)), 'eeee d MMMM');
                    $delivery->setDeliveryDate($dateAsTimeZone);

                    if (isset($firstShippingOption['deliveryTimeRange'])) {
                        $startTimeAsTimeZone = $this->timezoneInterface
                            ->date(new \DateTime($firstShippingOption['deliveryTimeRange']['lowerBound']))
                            ->format('H:i');
                        $endTimeAsTimeZone = $this->timezoneInterface
                            ->date(new \DateTime($firstShippingOption['deliveryTimeRange']['upperBound']))
                            ->format('H:i');
                        $delivery->setDeliveryWindowStart($startTimeAsTimeZone);
                        $delivery->setDeliveryWindowEnd($endTimeAsTimeZone);
                        $delivery->setDeliveryWindowText(__('%1 - %2', $startTimeAsTimeZone, $endTimeAsTimeZone));
                    }
                }

                if ($shippingOption['type'] == $result->getMethodCode()  && isset($shippingOption['servicePoints'])) {
                    $delivery = $this->deliveryFactory->create();

                    $firstServicePoint = $shippingOption['servicePoints']['servicePoint'][0];

                    if (isset($firstServicePoint['address'])) {
                        $delivery->setServicePointName($firstServicePoint['name']);
                        $delivery->setServicePointAddress($firstServicePoint['address']);
                        $delivery->setServicePointPostcode($firstServicePoint['postcode']);
                        $delivery->setServicePointCity($firstServicePoint['city']);
                        $delivery->setServicePointCode($firstServicePoint['code']);
                    }
                    else {
                        $delivery->setData([]);
                    }

                    $shippingMethodExtension->setDelivery($delivery);
                }
            }

            $shippingMethodExtension->setDelivery($delivery);

            $result->setExtensionAttributes($shippingMethodExtension);
        }

        if ($result->getCarrierCode() == 'paazlp') {
            if (isset($paazlData['delivery']) && isset($paazlData['delivery'][$result->getMethodCode()]) && isset($paazlData['delivery'][$result->getMethodCode()]['servicePoint'])) {
                $delivery = $this->deliveryFactory->create();

                if (isset($paazlData['delivery'][$result->getMethodCode()]['servicePoint']['address'])) {
                    $delivery->setServicePointName($paazlData['delivery'][$result->getMethodCode()]['servicePoint']['name']);
                    $delivery->setServicePointAddress($paazlData['delivery'][$result->getMethodCode()]['servicePoint']['address']);
                    $delivery->setServicePointPostcode($paazlData['delivery'][$result->getMethodCode()]['servicePoint']['postcode']);
                    $delivery->setServicePointCity($paazlData['delivery'][$result->getMethodCode()]['servicePoint']['city']);
                    $delivery->setServicePointCode($paazlData['delivery'][$result->getMethodCode()]['servicePoint']['code']);
                }
                else {
                    $delivery->setData([]);
                }

                $shippingMethodExtension->setDelivery($delivery);
                $shippingMethodExtension->setPaazlMethod('servicepoint');
            }
            elseif (isset($paazlData['delivery']) && isset($paazlData['delivery'][$result->getMethodCode()])) {
                $delivery = $this->deliveryFactory->create();

                if (isset($paazlData['delivery'][$result->getMethodCode()]['preferredDeliveryDate'])) {
                    $dateTime = $paazlData['delivery'][$result->getMethodCode()]['preferredDeliveryDate'];

                    $shippingOptions = $this->_paazlManagement->getShippingOptions();

                    foreach($shippingOptions as $shippingOption) {
                        if ($shippingOption['type'] == $result->getMethodCode()) {
                            foreach ($shippingOption['deliveryDates']['deliveryDateOption'] as $deliveryDateOption) {
                                if ($deliveryDateOption['deliveryDate'] == $dateTime && isset($deliveryDateOption['deliveryTimeRange'])) {
                                    $startTimeAsTimeZone = $this->timezoneInterface
                                        ->date(new \DateTime($deliveryDateOption['deliveryTimeRange']['lowerBound']))
                                        ->format('H:i');
                                    $endTimeAsTimeZone = $this->timezoneInterface
                                        ->date(new \DateTime($deliveryDateOption['deliveryTimeRange']['upperBound']))
                                        ->format('H:i');

                                    $delivery->setDeliveryWindowStart($startTimeAsTimeZone);
                                    $delivery->setDeliveryWindowEnd($endTimeAsTimeZone);
                                    $delivery->setDeliveryWindowText(__('%1 - %2', $startTimeAsTimeZone, $endTimeAsTimeZone));
                                }
                            }
                        }
                    }

                    $dateAsTimeZone = $this->timezoneInterface
                        ->date(new \DateTime($dateTime))
                        ->format('l j F');

                    $dateAsTimeZone = $this->dateTimeFormatter->formatObject($this->timezoneInterface->date(new \DateTime($dateTime)), 'eeee d MMMM');

                    $delivery->setDeliveryDate($dateAsTimeZone);
                }
                else {
                    $delivery->setData([]);
                }
                $shippingMethodExtension->setDelivery($delivery);
                $shippingMethodExtension->setPaazlMethod('delivery');
            }

            $result->setExtensionAttributes($shippingMethodExtension);
        }

        return $result;
    }
}
