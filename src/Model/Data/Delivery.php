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
}