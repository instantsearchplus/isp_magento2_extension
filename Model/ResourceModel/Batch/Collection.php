<?php

namespace Autocompleteplus\Autosuggest\Model\ResourceModel\Batch;
use Autocompleteplus\Autosuggest\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Autocompleteplus\Autosuggest\Model\Batch', 'Autocompleteplus\Autosuggest\Model\ResourceModel\Batch');
    }

    /**
     * Returns pairs batch_id - sku
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('product_id', 'sku');
    }
}