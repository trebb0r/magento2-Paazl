<?php

namespace Paazl\Shipping\Api\Data;

interface Delivery
{
    /**
     * @return array
     */
    public function getData();

    /**
     * @param $data
     * @return $this
     */
    public function setData($data);

    /**
     * @return array
     */
    public function getServicePointAddress();

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointAddress($data);
}