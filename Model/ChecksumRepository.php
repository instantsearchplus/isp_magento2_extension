<?php
/**
 * ChecksumRepository
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
use Autocompleteplus\Autosuggest\Api\ChecksumRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Autocompleteplus\Autosuggest\Model\ResourceModel\Checksum as ResourcePage;
use Autocompleteplus\Autosuggest\Model\ResourceModel\Checksum\CollectionFactory as ChecksumCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * ChecksumRepository
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
class ChecksumRepository implements ChecksumRepositoryInterface
{
    /**
     * @var ResourceChecksum
     */
    protected $resource;

    /**
     * @var ChecksumFactory
     */
    protected $checksumFactory;

    /**
     * @var ChecksumCollectionFactory
     */
    protected $checksumCollectionFactory;

    /**
     * @var Data\ChecksumSearchResultsInterfaceFactory
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
     * @var \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterfaceFactory
     */
    protected $dataChecksumFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourcePage $resource
     * @param ChecksumFactory $checksumFactory
     * @param Data\ChecksumInterfaceFactory $dataChecksumFactory
     * @param ChecksumCollectionFactory $checksumCollectionFactory
     * @param Data\ChecksumSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourcePage $resource,
        ChecksumFactory $checksumFactory,
        Data\ChecksumInterfaceFactory $dataChecksumFactory,
        ChecksumCollectionFactory $checksumCollectionFactory,
        Data\ChecksumSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->checksumFactory = $checksumFactory;
        $this->checksumCollectionFactory = $checksumCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataChecksumFactory = $dataChecksumFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Checksum data
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface $checksum
     * @return Page
     * @throws CouldNotSaveException
     */
    public function save(\Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface $checksum)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $checksum->setStoreId($storeId);
        try {
            $this->resource->save($page);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $page;
    }

    /**
     * Load Checksum data by given Checksum Identity
     *
     * @param string $pageId
     * @return Checksum
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($checksumId)
    {
        $checksum = $this->checksumFactory->create();
        $checksum->load($checksumId);
        if (!$checksum->getId()) {
            throw new NoSuchEntityException(__('Checksum with id "%1" does not exist.', $checksumId));
        }
        return $checksum;
    }

    /**
     * Load Checksum data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Autocompleteplus\Autosuggest\Model\ResourceModel\Checksum\Collection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->checksumCollectionFactory->create();
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
        /** @var Checksum $checksumModel */
        foreach ($collection as $checksumModel) {
            $pageData = $this->dataPageFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $pageData,
                $checksumModel->getData(),
                'Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface'
            );
            $pages[] = $this->dataObjectProcessor->buildOutputDataArray(
                $pageData,
                'Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface'
            );
        }
        $searchResults->setItems($pages);
        return $searchResults;
    }

    /**
     * Delete Checksum
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface $checksum
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface $checksum)
    {
        try {
            $this->resource->delete($checksum);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Checksum by given Checksum Identity
     *
     * @param string $checksumId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($checksumId)
    {
        return $this->delete($this->getById($checksumId));
    }
}
