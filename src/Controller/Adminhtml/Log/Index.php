<?php
/**
 * @package Paazl_Shipping
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Paazl\Shipping\Controller\Adminhtml\Log;

use Paazl\Shipping\Controller\Adminhtml\Log;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * @package Paazl\Shipping\Controller\Adminhtml\Log
 */
class Index extends Log
{
    /**
     * @return $this
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Paazl Log'));
        return $resultPage;
    }
}
