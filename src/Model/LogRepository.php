<?php
/**
 * @package Guapa_Paazl
 * @author Guapa <info@guapa.nl>
 * @copyright 2010-2017 Guapa B.V.
 */
namespace Guapa\Paazl\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Guapa\Paazl\Api\LogRepositoryInterface;
use Guapa\Paazl\Api\Data\LogInterface;
use Guapa\Paazl\Model\ResourceModel\Log as LogResource;
use Guapa\Paazl\Model\ResourceModel\Log\CollectionFactory;

/**
 * Class LogRepository
 * @package Guapa\Paazl\Model
 */
class LogRepository implements LogRepositoryInterface
{
    /** @var LogResource $logResource */
    protected $logResource;

    /** @var LogResource $logFactory */
    protected $logFactory;

    /** @var CollectionFactory $logCollectionFactory */
    protected $logCollectionFactory;

    /**
     * LogRepository constructor.
     * @param LogResource $log
     * @param LogFactory $logFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        LogResource $log,
        LogFactory $logFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->logResource = $log;
        $this->logFactory = $logFactory;
        $this->logCollectionFactory = $collectionFactory;
    }

    /**
     * @param LogInterface $log
     * @return mixed
     */
    public function save(LogInterface $log)
    {
        $this->logResource->save($log);
        return $log->getId();
    }

    /**
     * @param $logId
     * @return LogInterface
     * @throws NoSuchEntityException
     */
    public function getById($logId)
    {
        /** @var LogInterface $logItem */
        $log = $this->logFactory->create();
        $this->logResource->load($log, $logId);

        if(!$log->getLogId()) {
            throw new NoSuchEntityException(__('Log Item does not exist'));
        }
        return $log;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        // TODO: Implement getList() method.
    }

    /**
     * @param $logId
     * @return mixed
     */
    public function delete($logId)
    {
        $log = $this->logFactory->create();
        $log->setLogId($logId);
        if( $this->logResource->delete($log)){
            return true;
        } else {
            return false;
        }
    }
}
