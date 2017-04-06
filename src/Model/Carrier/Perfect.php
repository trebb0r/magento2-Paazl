<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Carrier;

class Perfect extends \Paazl\Shipping\Model\Carrier
{
    /** Paazl carrier code */
    const CODE = 'paazlperfect';

    /**
     * @return Result
     */
    protected function _getQuotes()
    {
        $this->_result = $this->_rateFactory->create();
        $this->_paazlData = $this->_paazlManagement->getPaazlData();

        if (isset($this->_paazlData['requests'])) {
            if (count($this->_paazlData['requests'])) {
                // Gather previous results from session
                foreach ($this->_paazlData['results'] as $method => $results) {
                    foreach ($results as $dataKey => $result) {
                        $this->_setCachedQuotes($dataKey, $result);
                    }
                }
                foreach ($this->_paazlData['requests'] as $requestMethod => $request) {
                    $response = $this->_getCachedQuotes($request->getRequestKey());
                    if (is_null($response)) {
                        $request = $this->_requestManager->doRequest($request);
                        $response = $request->getResponse();

                        if (!count($request->getErrors()) && !is_null($response)) {
                            if (!is_null($request->getIdentifier())) $response['identifier'] = $request->getIdentifier();
                            $this->_paazlData['results'][$requestMethod][$request->getRequestKey()] = $response;
                            $this->_setCachedQuotes($request->getRequestKey(), $response);
                        }
                    }
                }
            }
            unset ($this->_paazlData['requests']);
        }

        if (isset($this->_paazlData['results']['orderRequest']['success'])) {
            // Set order reference
            $this->_paazlData['orderReference'] = $this->_paazlManagement->_getQuoteId();
        }

        $checkoutStatusRequestData = [
            'context' => $this->_paazlManagement->_getQuoteId(),
            'body' => [
                'orderReference' => $this->_paazlManagement->_getQuoteId(),
            ]
        ];

        $requestMethod = 'checkoutStatusRequest';
        if (!isset($this->_paazlData['requests'][$requestMethod])) {
            $checkoutStatusRequest = $this->_requestBuilder->build('PaazlCheckoutStatusRequest', $checkoutStatusRequestData);
            $this->_paazlData['requests'][$requestMethod] = $checkoutStatusRequest;

            $request = $this->_requestManager->doRequest($checkoutStatusRequest);
            $checkoutStatusKey = $request->getRequestKey();
            $response = $request->getResponse();

            if (!count($request->getErrors()) && !is_null($response)) {
                if (!is_null($request->getIdentifier())) $response['identifier'] = $request->getIdentifier();
                $this->_paazlData['results'][$requestMethod][$request->getRequestKey()] = $response;
                $this->_setCachedQuotes($request->getRequestKey(), $response);
            }
        }

        $this->_paazlManagement->setPaazlData($this->_paazlData);

        $freeShippingThreshold = (float)$this->getConfigData('free_shipping_subtotal');
        $allowedMethods = $this->getAllowedMethods();

        // Choose something in Paazl Perfect?
        if (isset($this->_paazlData['results'][$requestMethod][$checkoutStatusKey]['callbackUrl'])) {
            if ($this->_paazlData['results'][$requestMethod][$checkoutStatusKey]['callbackUrl'] != "") {
                $data = $this->_paazlData['results'][$requestMethod][$checkoutStatusKey];
                $method = $data['delivery']['option'];

                $allMethods = parent::getAllowedMethods();
                if (isset($allMethods[$method])) {
                    $methodData = $allMethods[$method];
                    $title = $methodData['title'] . ' - ' . $data['delivery']['description'];
                }
                else {
                    $title = $data['delivery']['description'];
                }

                $methodPrice = (in_array($method, $this->getCode('free_methods')))
                    ? 0
                    : $data['delivery']['rate'];

                if ($freeShippingThreshold > 0) {
                    if ($this->_request->getPackageValueWithDiscount() > $freeShippingThreshold) {
                        if (in_array($method, $this->getCode('free_shipping_allowed_methods'))) {
                            $methodPrice = 0;
                        }
                    }
                }

                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier(static::CODE);
                $rate->setCarrierTitle(static::CODE);
                $rate->setMethod($method);
                $rate->setMethodTitle($title);
                $rate->setCost($methodPrice);
                $rate->setPrice($methodPrice);

                if ($data['delivery']['deliveryType'] == 'servicepoint') {
                    $this->_paazlData['delivery'] = $data['delivery'];
                    $this->_paazlManagement->setPaazlData($this->_paazlData);
                }

                $this->_result->append($rate);

                return $this->_result;
            }
        }

        foreach ($allowedMethods as $method => $methodData) {
            $methodPrice = (in_array($method, $this->getCode('free_methods')))
                ? 0
                : $methodData['price'];

            if ($freeShippingThreshold > 0) {
                if ($this->_request->getPackageValueWithDiscount() > $freeShippingThreshold) {
                    if (in_array($method, $this->getCode('free_shipping_allowed_methods'))) {
                        $methodPrice = 0;
                    }
                }
            }

            $rate = $this->_rateMethodFactory->create();
            $rate->setCarrier(static::CODE);
            //$rate->setCarrierTitle($methodData['title']);
            $rate->setCarrierTitle(static::CODE);
            $rate->setMethod($method);
            $rate->setMethodTitle($methodData['method']);
            $rate->setCost($methodPrice);
            $rate->setPrice($methodPrice);

            $this->_result->append($rate);
        }

        return $this->_result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        $methods = parent::getAllowedMethods();

        uasort($methods, ["\Paazl\Shipping\Model\Carrier\Perfect", "cmp"]);

        $key = key($methods);
        return [$key => array_shift($methods)];
    }

    private function cmp($a, $b)
    {
        if ($a['price'] == $b['price']) {
            return 0;
        }
        return ($a['price'] < $b['price']) ? -1 : 1;
    }
}