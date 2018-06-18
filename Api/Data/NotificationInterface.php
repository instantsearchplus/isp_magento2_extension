<?php

namespace Autocompleteplus\Autosuggest\Api\Data;

interface NotificationInterface
{
    const ENTITY_ID = 'entity_id';
    const TYPE = 'type';
    const SUBJECT = 'subject';
    const MESSAGE = 'message';
    const TIMESTAMP = 'timestamp';
    const IS_ACTIVE = 'is_active';

    public function getId();
    public function getType();
    public function getSubject();
    public function getMessage();
    public function getTimestamp();
    public function isActive();

    public function setId($id);
    public function setType($type);
    public function setSubject($subject);
    public function setMessage($message);
    public function setTimestamp($timestamp);
    public function setIsActive($isActive);
}
