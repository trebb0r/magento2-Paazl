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
     * @return string|null
     */
    public function getServicePointAddress()
    {
        return isset($this->_data['service_point_address'])? $this->_data['service_point_address'] : null;
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
     * @return string|null
     */
    public function getServicePointName()
    {
        return isset($this->_data['service_point_name'])? $this->_data['service_point_name'] : null;
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
     * @return string|null
     */
    public function getServicePointPostcode()
    {
        return isset($this->_data['service_point_postcode'])? $this->_data['service_point_postcode'] : null;
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
     * @return string|null
     */
    public function getServicePointCity()
    {
        return isset($this->_data['service_point_city'])? $this->_data['service_point_city'] : null;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setServicePointCity($data)
    {
        $this->_data['service_point_city'] = $data;
    }

    /**
     * @return string|null
     */
    public function getDeliveryDate()
    {
        return isset($this->_data['delivery_date'])? $this->_data['delivery_date'] : null;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setDeliveryDate($data)
    {
        $this->_data['delivery_date'] = $data;
    }

    /**
     * @return string|null
     */
    public function getDeliveryWindowStart()
    {
        return isset($this->_data['delivery_window_start'])? $this->_data['delivery_window_start'] : null;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setDeliveryWindowStart($data)
    {
        $this->_data['delivery_window_start'] = $data;
    }

    /**
     * @return string|null
     */
    public function getDeliveryWindowEnd()
    {
        return isset($this->_data['delivery_window_end'])? $this->_data['delivery_window_end'] : null;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setDeliveryWindowEnd($data)
    {
        $this->_data['delivery_window_end'] = $data;
    }
}