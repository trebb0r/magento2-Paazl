<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Model\Carrier;

class Perfect extends \Paazl\Shipping\Model\Carrier
{
    /** Paazl carrier code */
    const CODE = 'paazlperfect';

    /**
     * @return Result
     */
    protected function _getQuotes()
    {
        return parent::_getQuotes();
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        $methods = parent::getAllowedMethods();

        uasort($methods, ["\Paazl\Shipping\Model\Carrier\Perfect", "cmp"]);

        $key = key($methods);
        return [$key => array_shift($methods)];
    }

    private function cmp($a, $b)
    {
        if ($a['price'] == $b['price']) {
            return 0;
        }
        return ($a['price'] < $b['price']) ? -1 : 1;
    }
}