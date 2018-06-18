<?php

namespace Autocompleteplus\Autosuggest\Api\Data;

interface ChecksumInterface
{
    const ENTITY_ID = 'entity_id';
    const SKU = 'sku';
    const PRODUCT_ID = 'product_id';
    const STORE_ID = 'store_id';
    const CHECKSUM = 'checksum';

    public function getId();
    public function getSku();
    public function getProductId();
    public function getStoreId();
    public function getChecksum();

    public function setId($id);
    public function setSku($sku);
    public function setProductId($id);
    public function setStoreId($storeId);
    public function setChecksum($checksum);
}
