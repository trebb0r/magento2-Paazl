<?php
/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Guapa\Paazl\Model\Source\Method;

class Generic
{
    /** @var \Guapa\Paazl\Model\Carrier */
    protected $paazlCarrier;

    /** @var string */
    protected $_code = '';

    /**
     * @param \Guapa\Paazl\Model\Carrier $carrier
     */
    public function __construct(\Guapa\Paazl\Model\Carrier $carrier)
    {
        $this->paazlCarrier = $carrier;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $configData = $this->paazlCarrier->getCode($this->_code);
        $arr = [];
        foreach ($configData as $code => $title) {
            $arr[] = ['value' => $code, 'label' => $title];
        }
        return $arr;
    }
}
