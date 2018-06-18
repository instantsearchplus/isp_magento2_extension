<?php

namespace Autocompleteplus\Autosuggest\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Autosuggest Notification CRUD interface.
 * @api
 */
interface NotificationRepositoryInterface
{
    /**
     * Save Notification.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\NotificationInterface $notification
     * @return \Autocompleteplus\Autosuggest\Api\Data\NotificationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\NotificationInterface $notification);

    /**
     * Retrieve Notification.
     *
     * @param int $notificationId
     * @return \Autocompleteplus\Autosuggest\Api\Data\NotificationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($notificationId);

    /**
     * Retrieve Notifications matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Autocompleteplus\Autosuggest\Api\Data\NotificationSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Notification.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\NotificationInterface $notification
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\NotificationInterface $notification);

    /**
     * Delete Notification by ID.
     *
     * @param int $notificationId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($notificationId);
}
