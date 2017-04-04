<?php
/**
 * @package Paazl_Shipping
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Paazl\Shipping\Model\Source;

class Method implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'a', 'label' => __('A')],
            ['value' => 'b', 'label' => __('B')]
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return ['a' => __('A'), 'b' => __('B')];
    }
}
