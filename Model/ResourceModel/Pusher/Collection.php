<?php

namespace Autocompleteplus\Autosuggest\Model\ResourceModel\Pusher;

use Autocompleteplus\Autosuggest\Model\ResourceModel\AbstractCollection as AbstractCollection;

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
        $this->_init('Autocompleteplus\Autosuggest\Model\Pusher', 'Autocompleteplus\Autosuggest\Model\ResourceModel\Pusher');
    }

    /**
     * Returns pairs batch_id - sku
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('identifier', 'checksum');
    }
}
