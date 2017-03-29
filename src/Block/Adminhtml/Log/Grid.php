<?php
/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Guapa\Paazl\Block\Adminhtml\Log;

/**
 * Class Grid
 * @package Guapa\Paazl\Block\Adminhtml\Log
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Guapa_Paazl';
        $this->_controller = 'adminhtml_log';
        $this->_headerText = __('Paazl Log');

        parent::_construct();
        $this->buttonList->remove('add');
    }
}
