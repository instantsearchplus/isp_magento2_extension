<?php
namespace Autocompleteplus\Autosuggest\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for Autosuggest Checksum search results.
 * @api
 */
interface ChecksumSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get checksums list.
     *
     * @return \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface[]
     */
    public function getItems();

    /**
     * Set checksums list.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
