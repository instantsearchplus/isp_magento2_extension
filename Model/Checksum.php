<?php
namespace Autocompleteplus\Autosuggest\Model;

use Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Checksum extends \Magento\Framework\Model\AbstractModel implements ChecksumInterface, IdentityInterface
{
    /**
     * Autosuggest batch cache tag
     */
    const CACHE_TAG = 'autosuggest_checksum';

    /**
     * @var string
     */
    protected $_cacheTag = 'autosuggest_checksum';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'autosuggest_checksum';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Autocompleteplus\Autosuggest\Model\ResourceModel\Checksum');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getChecksum()];
    }

    /**
     * Retrieve batch id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    public function getChecksum()
    {
        return $this->getData(self::CHECKSUM);
    }

    public function setId($id)
    {
        $this->setData(self::ENTITY_ID, $id);
        return $this;
    }

    public function setSku($sku)
    {
        $this->setData(self::SKU, $sku);
        return $this;
    }

    public function setProductId($id)
    {
        $this->setData(self::PRODUCT_ID, $id);
        return $this;
    }

    public function setStoreId($storeId)
    {
        $this->setData(self::STORE_ID, $storeId);
        return $this;
    }

    public function setChecksum($checksum)
    {
        $this->setData(self::CHECKSUM, $checksum);
        return $this;
    }
}
