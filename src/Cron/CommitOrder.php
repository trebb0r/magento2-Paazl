<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */
namespace Paazl\Shipping\Cron;

use Magento\Store\Model\ScopeInterface as StoreScopeInterface;

class CommitOrder
{
    /** Webshop Identifier config path */
    const XML_PATH_CRON_COMMIT_ORDERS = 'paazl/order/cron_export';
    const XML_PATH_ASSURED_AMOUNT = 'paazl/order/assured_amount';

    /** @var \Magento\Sales\Api\OrderRepositoryInterface */
    protected $_orderRepository;

    /** @var \Magento\Sales\Model\OrderFactory */
    protected $_orderFactory;

    /** @var \Magento\Sales\Model\ResourceModel\Order */
    protected $_orderResource;

    /** @var \Paazl\Shipping\Model\Api\RequestBuilder */
    protected $_requestBuilder;

    /** @var \Paazl\Shipping\Model\Api\RequestManager */
    protected $_requestManager;

    /** @var \Magento\Framework\Api\SearchCriteriaBuilder */
    protected $_searchCriteriaBuilder;

    /** @var \Magento\Framework\Api\FilterBuilder */
    protected $_filterBuilder;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;

    /**
     * @var \Paazl\Shipping\Model\PaazlManagement
     */
    protected $paazlManagement;

    /** @var \Psr\Log\LoggerInterface */
    protected $_logger;

    /**
     * CommitOrder constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResource
     * @param \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder
     * @param \Paazl\Shipping\Model\Api\RequestManager $requestManager
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Paazl\Shipping\Model\PaazlManagement $paazlManagement
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResource,
        \Paazl\Shipping\Model\Api\RequestBuilder $requestBuilder,
        \Paazl\Shipping\Model\Api\RequestManager $requestManager,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Paazl\Shipping\Model\PaazlManagement $paazlManagement,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_orderFactory = $orderFactory;
        $this->_orderResource = $orderResource;
        $this->_requestBuilder = $requestBuilder;
        $this->_requestManager = $requestManager;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->paazlManagement = $paazlManagement;
        $this->_logger = $logger;
    }

    public function execute()
    {
        if ($this->_scopeConfig->isSetFlag(self::XML_PATH_CRON_COMMIT_ORDERS)) {
            // Orders with ext_order_id 0 are flagged for export
            $filterData = [
                ['ext_order_id', 0, 'eq']
            ];
            $filters = $this->_buildFilters($filterData);
            $searchCriteria = $this->_buildSearchCriteria($filters);
            $orders = $this->_orderRepository->getList($searchCriteria);

            foreach ($orders as $orderToCommit) {
                $order = $this->_orderFactory->create()->loadByIncrementId($orderToCommit->getIncrementId());

                if ($order->getExtOrderId() == 'error-1002') continue;

                $extOrderId = $this->paazlManagement->getReferencePrefix() . $order->getIncrementId();

                $response = $this->paazlManagement->processOrderCommitRequest($order);
                if (isset($response['success'])) {
                    $order->setExtOrderId($extOrderId);
                    $this->_orderResource->save($order);
                } else {
                    if (isset($response['error']['code']) && $response['error']['code'] == '1002') {
                        //@todo create new order reference and redo commit
                        $order->setExtOrderId('error-1002');
                        $this->_orderResource->save($order);
                    }
                }
            }
        }
    }

    /**
     * [['field', 'value']['field2', 'value2', 'like']]
     *
     * @param array $customFilters
     * @return array
     */
    protected function _buildFilters($customFilters = [])
    {
        $filters = [];

        foreach ($customFilters as $key => $data) {
            list($field, $value, $conditionType) = $data;

            if (is_null($conditionType)) $conditionType = 'eq';

            $newFilter = $this->_filterBuilder
                ->setField($field)
                ->setValue($value)
                ->setConditionType($conditionType)
                ->create();

            $filters[] = $newFilter;
        }

        return $filters;
    }

    /**
     * @param array $filters
     * @return \Magento\Framework\Api\SearchCriteria
     */
    protected function _buildSearchCriteria(array $filters = [])
    {
        $searchCriteria = $this->_searchCriteriaBuilder->addFilters($filters)->create();
        return $searchCriteria;
    }
}
