<?php

namespace Autocompleteplus\Autosuggest\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Autosuggest Checksum CRUD interface.
 * @api
 */
interface ChecksumRepositoryInterface
{
    /**
     * Save Checksum.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface $checksum
     * @return \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\ChecksumInterface $checksum);

    /**
     * Retrieve Checksum.
     *
     * @param int $checksumId
     * @return \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($checksumId);

    /**
     * Retrieve Checksums matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Autocompleteplus\Autosuggest\Api\Data\ChecksumSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Checksum.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface $checksum
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\ChecksumInterface $checksum);

    /**
     * Delete Checksum by ID.
     *
     * @param int $checksumId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($checksumId);
}
