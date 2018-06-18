<?php

namespace Autocompleteplus\Autosuggest\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Autosuggest Pusher CRUD interface.
 * @api
 */
interface PusherRepositoryInterface
{
    /**
     * Save Pusher.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\PusherInterface $pusher
     * @return \Autocompleteplus\Autosuggest\Api\Data\PusherInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\PusherInterface $pusher);

    /**
     * Retrieve Pusher.
     *
     * @param int $pusherId
     * @return \Autocompleteplus\Autosuggest\Api\Data\PusherInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($pusherId);

    /**
     * Retrieve Pushers matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Autocompleteplus\Autosuggest\Api\Data\PusherSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Pusher.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\PusherInterface $pusher
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\PusherInterface $pusher);

    /**
     * Delete Pusher by ID.
     *
     * @param int $pusherId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($pusherId);
}
