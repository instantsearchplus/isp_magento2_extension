<?php
namespace Autocompleteplus\Autosuggest\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for Autosuggest Batch search results.
 * @api
 */
interface NotificationSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get notifications list.
     *
     * @return \Autocompleteplus\Autosuggest\Api\Data\NotificationInterface[]
     */
    public function getItems();

    /**
     * Set notifications list.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\NotificationInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
