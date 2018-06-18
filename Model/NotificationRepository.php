<?php
/**
 * NotificationRepository File
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
use Autocompleteplus\Autosuggest\Api\NotificationRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Autocompleteplus\Autosuggest\Model\ResourceModel\Notification as ResourcePage;
use Autocompleteplus\Autosuggest\Model\ResourceModel\Notification\CollectionFactory as NotificationCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * NotificationRepository
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
class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * @var ResourceNotification
     */
    protected $resource;

    /**
     * @var NotificationFactory
     */
    protected $notificationFactory;

    /**
     * @var NotificationCollectionFactory
     */
    protected $notificationCollectionFactory;

    /**
     * @var Data\NotificationSearchResultsInterfaceFactory
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
     * @var \Autocompleteplus\Autosuggest\Api\Data\NotificationInterfaceFactory
     */
    protected $dataNotificationFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ResourcePage $resource
     * @param NotificationFactory $notificationFactory
     * @param Data\NotificationInterfaceFactory $dataNotificationFactory
     * @param NotificationCollectionFactory $notificationCollectionFactory
     * @param Data\NotificationSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourcePage $resource,
        NotificationFactory $notificationFactory,
        Data\NotificationInterfaceFactory $dataNotificationFactory,
        NotificationCollectionFactory $notificationCollectionFactory,
        Data\NotificationSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->notificationFactory = $notificationFactory;
        $this->notificationCollectionFactory = $notificationCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataNotificationFactory = $dataNotificationFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Notification data
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\NotificationInterface $notification
     * @return Page
     * @throws CouldNotSaveException
     */
    public function save(\Autocompleteplus\Autosuggest\Api\Data\NotificationInterface $notification)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $notification->setStoreId($storeId);
        try {
            $this->resource->save($page);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $page;
    }

    /**
     * Load Notification data by given Notification Identity
     *
     * @param string $pageId
     * @return Notification
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($notificationId)
    {
        $notification = $this->notificationFactory->create();
        $notification->load($notificationId);
        if (!$notification->getId()) {
            throw new NoSuchEntityException(__('Notification with id "%1" does not exist.', $notificationId));
        }
        return $notification;
    }

    /**
     * Load Notification data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Autocompleteplus\Autosuggest\Model\ResourceModel\Notification\Collection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->notificationCollectionFactory->create();
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
        /** @var Notification $notificationModel */
        foreach ($collection as $notificationModel) {
            $pageData = $this->dataPageFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $pageData,
                $notificationModel->getData(),
                'Autocompleteplus\Autosuggest\Api\Data\NotificationInterface'
            );
            $pages[] = $this->dataObjectProcessor->buildOutputDataArray(
                $pageData,
                'Autocompleteplus\Autosuggest\Api\Data\NotificationInterface'
            );
        }
        $searchResults->setItems($pages);
        return $searchResults;
    }

    /**
     * Delete Notification
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\NotificationInterface $notification
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Autocompleteplus\Autosuggest\Api\Data\NotificationInterface $notification)
    {
        try {
            $this->resource->delete($notification);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Notification by given Notification Identity
     *
     * @param string $notificationId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($notificationId)
    {
        return $this->delete($this->getById($notificationId));
    }
}
