<?php
/**
 * @package Paazl_Shipping
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Paazl\Shipping\Model\ResourceModel\Log;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Paazl\Shipping\Model\ResourceModel\Log
 */
class Collection extends AbstractCollection
{
    /**
    * Define resource model
    *
    * @return void
    */
    protected function _construct() {
        $this->_init('Paazl\Shipping\Model\Log', 'Paazl\Shipping\Model\ResourceModel\Log');
    }
}
