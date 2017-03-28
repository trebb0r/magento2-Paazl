<?php
/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Guapa\Paazl\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Log
 * @package Guapa\Paazl\Model\ResourceModel
 */
class Log extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('paazl_log', 'log_id');
    }
}
