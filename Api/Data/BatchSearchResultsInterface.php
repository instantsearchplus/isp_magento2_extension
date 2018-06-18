<?php
namespace Autocompleteplus\Autosuggest\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for Autosuggest Batch search results.
 * @api
 */
interface BatchSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get batches list.
     *
     * @return \Autocompleteplus\Autosuggest\Api\Data\BatchInterface[]
     */
    public function getItems();

    /**
     * Set batches list.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\BatchInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
