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


    /**
     * @return array
     */
    public function getServicePointName();

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointName($data);


    /**
     * @return array
     */
    public function getServicePointPostcode();

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointPostcode($data);

    /**
     * @return array
     */
    public function getServicePointCity();

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointCity($data);
}