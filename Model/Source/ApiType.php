<?php
/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Guapa\Paazl\Model\Source;

class ApiType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Staging')],
            ['value' => 1, 'label' => __('Production')]
        ];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Staging'), 1 => __('Production')];
    }
}
