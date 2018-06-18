<?php
/**
 * Batch File
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category Mage
 *
 * @package   Instantsearchplus
 * @author    Fast Simon <info@instantsearchplus.com>
 * @copyright 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Model;

use Autocompleteplus\Autosuggest\Api\Data\BatchInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Batch
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @category Mage
 *
 * @package   Instantsearchplus
 * @author    Fast Simon <info@instantsearchplus.com>
 * @copyright 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class Batch extends \Magento\Framework\Model\AbstractModel implements BatchInterface, IdentityInterface
{
    /**
     * Autosuggest batch cache tag
     */
    const CACHE_TAG = 'autosuggest_batch';

    /**
     * @var string
     */
    protected $_cacheTag = 'autosuggest_batch';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'autosuggest_batch';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Autocompleteplus\Autosuggest\Model\ResourceModel\Batch');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getProductId()];
    }

    /**
     * Retrieve batch id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::BATCH_ID);
    }

    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    public function getAction()
    {
        return $this->getData(self::ACTION);
    }

    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    public function setId($id)
    {
        $this->setData(self::BATCH_ID, $id);
        return $this;
    }

    public function setProductId($productId)
    {
        $this->setData(self::PRODUCT_ID, $productId);
        return $this;
    }

    public function setStoreId($storeId)
    {
        $this->setData(self::STORE_ID, $storeId);
        return $this;
    }

    public function setUpdateTime($updateTime)
    {
        $this->setData(self::UPDATE_TIME, $updateTime);
        return $this;
    }

    public function setAction($action)
    {
        $this->setData(self::ACTION, $action);
        return $this;
    }

    public function setSku($sku)
    {
        $this->setData(self::SKU, $sku);
        return $this;
    }
}
