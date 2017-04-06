<?php

namespace Paazl\Shipping\Model\Data;

class Delivery implements \Paazl\Shipping\Api\Data\Delivery
{
    protected $_data;

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * @return array
     */
    public function getServicePointAddress()
    {
        return $this->_data['service_point_address'];
    }

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointAddress($data)
    {
        $this->_data['service_point_address'] = $data;
    }

    /**
     * @return array
     */
    public function getServicePointName()
    {
        return $this->_data['service_point_name'];
    }

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointName($data)
    {
        $this->_data['service_point_name'] = $data;
    }

    /**
     * @return array
     */
    public function getServicePointPostcode()
    {
        return $this->_data['service_point_postcode'];
    }

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointPostcode($data)
    {
        $this->_data['service_point_postcode'] = $data;
    }

    /**
     * @return array
     */
    public function getServicePointCity()
    {
        return $this->_data['service_point_city'];
    }

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointCity($data)
    {
        $this->_data['service_point_city'] = $data;
    }
}