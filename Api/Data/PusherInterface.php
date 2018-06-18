<?php

namespace Autocompleteplus\Autosuggest\Api\Data;

interface PusherInterface
{
    const ENTITY_ID = 'entity_id';
    const STORE_ID = 'store_id';
    const TO_SEND = 'to_send';
    const OFFSET = 'offset';
    const TOTAL_BATCHES = 'total_batches';
    const BATCH_NUMBER = 'batch_number';
    const SENT = 'sent';

    public function getId();
    public function getStoreId();
    public function getToSend();
    public function getOffset();
    public function getTotalBatches();
    public function getBatchNumber();
    public function getSent();

    public function setId($id);
    public function setStoreId($storeId);
    public function setToSend($toSend);
    public function setOffset($offset);
    public function setTotalBatches($totalBatches);
    public function setBatchNumber($batchNumber);
    public function setSent($sent);
}
