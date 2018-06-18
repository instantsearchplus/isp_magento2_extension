<?php
/**
 * PusherRepository File
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
use Autocompleteplus\Autosuggest\Api\PusherRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Autocompleteplus\Autosuggest\Model\ResourceModel\Pusher as ResourcePage;
use Autocompleteplus\Autosuggest\Model\ResourceModel\Pusher\CollectionFactory as PusherCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * PusherRepository
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
class PusherRepository implements PusherRepositoryInterface
{
    /**
     * @var ResourcePusher
     */
    protected $resource;

    /**
     * @var PusherFactory
     */
    protected $pusherFactory;

    /**
     * @var PusherCollectionFactory
     */
    protected $pusherCollectionFactory;

    /**
     * @var Data\PusherSearchResultsInterfaceFactory
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
     * @var \Autocompleteplus\Autosuggest\Api\Data\PusherInterfaceFactory
     */
    protected $dataPusherFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ResourcePage $resource
     * @param PusherFactory $pusherFactory
     * @param Data\PusherInterfaceFactory $dataPusherFactory
     * @param PusherCollectionFactory $pusherCollectionFactory
     * @param Data\PusherSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourcePage $resource,
        PusherFactory $pusherFactory,
        Data\PusherInterfaceFactory $dataPusherFactory,
        PusherCollectionFactory $pusherCollectionFactory,
        Data\PusherSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->pusherFactory = $pusherFactory;
        $this->pusherCollectionFactory = $pusherCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPusherFactory = $dataPusherFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Pusher data
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\PusherInterface $pusher
     * @return Page
     * @throws CouldNotSaveException
     */
    public function save(\Autocompleteplus\Autosuggest\Api\Data\PusherInterface $pusher)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $pusher->setStoreId($storeId);
        try {
            $this->resource->save($page);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $page;
    }

    /**
     * Load Pusher data by given Pusher Identity
     *
     * @param string $pageId
     * @return Pusher
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($pusherId)
    {
        $pusher = $this->pusherFactory->create();
        $pusher->load($pusherId);
        if (!$pusher->getId()) {
            throw new NoSuchEntityException(__('Pusher with id "%1" does not exist.', $pusherId));
        }
        return $pusher;
    }

    /**
     * Load Pusher data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Autocompleteplus\Autosuggest\Model\ResourceModel\Pusher\Collection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->pusherCollectionFactory->create();
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
        /** @var Pusher $pusherModel */
        foreach ($collection as $pusherModel) {
            $pageData = $this->dataPageFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $pageData,
                $pusherModel->getData(),
                'Autocompleteplus\Autosuggest\Api\Data\PusherInterface'
            );
            $pages[] = $this->dataObjectProcessor->buildOutputDataArray(
                $pageData,
                'Autocompleteplus\Autosuggest\Api\Data\PusherInterface'
            );
        }
        $searchResults->setItems($pages);
        return $searchResults;
    }

    /**
     * Delete Pusher
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\PusherInterface $pusher
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Autocompleteplus\Autosuggest\Api\Data\PusherInterface $pusher)
    {
        try {
            $this->resource->delete($pusher);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Pusher by given Pusher Identity
     *
     * @param string $pusherId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($pusherId)
    {
        return $this->delete($this->getById($pusherId));
    }
}
