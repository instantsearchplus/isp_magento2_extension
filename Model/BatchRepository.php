<?php
/**
 * BatchRepository File
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category Mage
 *
 * @package   Instantsearchplus
 * @author    Fast Simon <info@instantsearchplus.com>
 * @copyright 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Model;

use Autocompleteplus\Autosuggest\Api\Data;
use Autocompleteplus\Autosuggest\Api\BatchRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Autocompleteplus\Autosuggest\Model\ResourceModel\Batch as ResourcePage;
use Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory as BatchCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * BatchRepository
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category Mage
 *
 * @package   Instantsearchplus
 * @author    Fast Simon <info@instantsearchplus.com>
 * @copyright 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class BatchRepository implements BatchRepositoryInterface
{
    /**
     * @var ResourceBatch
     */
    protected $resource;

    /**
     * @var BatchFactory
     */
    protected $batchFactory;

    /**
     * @var BatchCollectionFactory
     */
    protected $batchCollectionFactory;

    /**
     * @var Data\BatchSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Autocompleteplus\Autosuggest\Api\Data\BatchInterfaceFactory
     */
    protected $dataBatchFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ResourcePage $resource
     * @param BatchFactory $batchFactory
     * @param Data\BatchInterfaceFactory $dataBatchFactory
     * @param BatchCollectionFactory $batchCollectionFactory
     * @param Data\BatchSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourcePage $resource,
        BatchFactory $batchFactory,
        Data\BatchInterfaceFactory $dataBatchFactory,
        BatchCollectionFactory $batchCollectionFactory,
        Data\BatchSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->batchFactory = $batchFactory;
        $this->batchCollectionFactory = $batchCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataBatchFactory = $dataBatchFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Batch data
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\BatchInterface $batch
     * @return Page
     * @throws CouldNotSaveException
     */
    public function save(\Autocompleteplus\Autosuggest\Api\Data\BatchInterface $batch)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $batch->setStoreId($storeId);
        try {
            $this->resource->save($page);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $page;
    }

    /**
     * Load Batch data by given Batch Identity
     *
     * @param string $pageId
     * @return Batch
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($batchId)
    {
        $batch = $this->batchFactory->create();
        $batch->load($batchId);
        if (!$batch->getId()) {
            throw new NoSuchEntityException(__('Batch with id "%1" does not exist.', $batchId));
        }
        return $batch;
    }

    /**
     * Load Batch data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\Collection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->batchCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $pages = [];
        /** @var Batch $batchModel */
        foreach ($collection as $batchModel) {
            $pageData = $this->dataPageFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $pageData,
                $batchModel->getData(),
                'Autocompleteplus\Autosuggest\Api\Data\BatchInterface'
            );
            $pages[] = $this->dataObjectProcessor->buildOutputDataArray(
                $pageData,
                'Autocompleteplus\Autosuggest\Api\Data\BatchInterface'
            );
        }
        $searchResults->setItems($pages);
        return $searchResults;
    }

    /**
     * Delete Batch
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\BatchInterface $batch
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Autocompleteplus\Autosuggest\Api\Data\BatchInterface $batch)
    {
        try {
            $this->resource->delete($batch);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Batch by given Batch Identity
     *
     * @param string $batchId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($batchId)
    {
        return $this->delete($this->getById($batchId));
    }
}
