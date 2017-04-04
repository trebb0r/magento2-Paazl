<?php
/**
 * @package Paazl_Shipping
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Paazl\Shipping\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Paazl\Shipping\Api\Data\LogInterface;

/**
 * @api
 * Interface QueueRepositoryInterface
 * @package Paazl\Shipping\Api
 */
interface LogRepositoryInterface
{
    /**
     * @param LogInterface $log
     * @return mixed
     */
    public function save(LogInterface $log);

    /**
     * @param $logId
     * @return LogInterface
     * @throws NoSuchEntityException
     */
    public function getById($logId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param $logId
     * @return mixed
     */
    public function delete($logId);
}
