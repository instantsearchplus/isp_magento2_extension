<?php
namespace Autocompleteplus\Autosuggest\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for Autosuggest Pusher search results.
 * @api
 */
interface PusherSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get pusher list.
     *
     * @return \Autocompleteplus\Autosuggest\Api\Data\PusherInterface[]
     */
    public function getItems();

    /**
     * Set pusher list.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\PusherInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
