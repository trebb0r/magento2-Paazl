<?php

/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Api;

interface PaazlManagementInterface
{

    /**
     * @return array
     */
    public function getMapping();

    /**
     * @return string
     */
    public function getReferencePrefix();

    /**
     * @param $weight
     * @return float
     */
    public function getConvertedWeight($weight);

    /**
     * @param $order
     * @return mixed
     */
    public function processOrderCommitRequest($order);

    /**
     * @param $dateTime
     * @return mixed
     */
    public function processListOrdersRequest($dateTime);
}