<?php

namespace Autocompleteplus\Autosuggest\Model\ResourceModel\Checksum;

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
        $this->_init('Autocompleteplus\Autosuggest\Model\Checksum', 'Autocompleteplus\Autosuggest\Model\ResourceModel\Checksum');
    }

    /**
     * Returns pairs batch_id - sku
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('entity_id', 'checksum');
    }
}
