<?php

namespace Autocompleteplus\Autosuggest\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Router\ActionList\Reader;
/**
 * Autosuggest Batch CRUD interface.
 * @api
 */
interface BatchRepositoryInterface
{
    /**
     * Save Batch.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\BatchInterface $batch
     * @return \Autocompleteplus\Autosuggest\Api\Data\BatchInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\BatchInterface $batch);

    /**
     * Retrieve Batch.
     *
     * @param int $batchId
     * @return \Autocompleteplus\Autosuggest\Api\Data\BatchInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($batchId);

    /**
     * Retrieve Batchs matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Autocompleteplus\Autosuggest\Api\Data\BatchSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Batch.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\BatchInterface $batch
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\BatchInterface $batch);

    /**
     * Delete Batch by ID.
     *
     * @param int $batchId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($batchId);
}
