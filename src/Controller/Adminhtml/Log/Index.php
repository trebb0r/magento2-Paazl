<?php
/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Guapa\Paazl\Controller\Adminhtml\Log;

use Guapa\Paazl\Controller\Adminhtml\Log;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * @package Guapa\Paazl\Controller\Adminhtml\Log
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
