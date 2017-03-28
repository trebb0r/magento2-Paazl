<?php
/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Guapa\Paazl\Model\ResourceModel\Log;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Guapa\Paazl\Model\ResourceModel\Log
 */
class Collection extends AbstractCollection
{
    /**
    * Define resource model
    *
    * @return void
    */
    protected function _construct() {
        $this->_init('Guapa\Paazl\Model\Log', 'Guapa\Paazl\Model\ResourceModel\Log');
    }
}
