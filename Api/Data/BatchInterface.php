<?php

namespace Autocompleteplus\Autosuggest\Api\Data;

interface BatchInterface
{
    const BATCH_ID = 'entity_id';
    const PRODUCT_ID = 'product_id';
    const STORE_ID = 'store_id';
    const UPDATE_TIME = 'update_time';
    const ACTION = 'action';
    const SKU = 'sku';

    public function getId();
    public function getProductId();
    public function getStoreId();
    public function getUpdateTime();
    public function getAction();
    public function getSku();

    public function setId($id);
    public function setProductId($productId);
    public function setStoreId($storeId);
    public function setUpdateTime($updateTime);
    public function setAction($action);
    public function setSku($sku);
}
