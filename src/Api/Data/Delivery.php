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
     * @return string|null
     */
    public function getServicePointAddress();

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointAddress($data);


    /**
     * @return string|null
     */
    public function getServicePointName();

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointName($data);


    /**
     * @return string|null
     */
    public function getServicePointPostcode();

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointPostcode($data);

    /**
     * @return string|null
     */
    public function getServicePointCity();

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointCity($data);

    /**
     * @return string|null
     */
    public function getDeliveryDate();

    /**
     * @param $data
     * @return $this
     */
    public function setDeliveryDate($data);

    /**
     * @return string|null
     */
    public function getDeliveryWindowStart();

    /**
     * @param $data
     * @return $this
     */
    public function setDeliveryWindowStart($data);

    /**
     * @return string|null
     */
    public function getDeliveryWindowEnd();

    /**
     * @param $data
     * @return $this
     */
    public function setDeliveryWindowEnd($data);

    /**
     * @return string|null
     */
    public function getDeliveryWindowText();

    /**
     * @param $data
     * @return $this
     */
    public function setDeliveryWindowText($data);

    /**
     * @return string|null
     */
    public function getServicePointCode();

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointCode($data);
}