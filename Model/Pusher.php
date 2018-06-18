<?php
/**
 * Pusher File
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

use Autocompleteplus\Autosuggest\Api\Data\PusherInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Pusher
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
class Pusher extends \Magento\Framework\Model\AbstractModel implements PusherInterface, IdentityInterface
{
    /**
     * Autosuggest notification cache tag
     */
    const CACHE_TAG = 'autosuggest_pusher';

    /**
     * @var string
     */
    protected $_cacheTag = 'autosuggest_pusher';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'autosuggest_pusher';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Autocompleteplus\Autosuggest\Model\ResourceModel\Pusher');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    public function getToSend()
    {
        return $this->getData(self::TO_SEND);
    }

    public function getOffset()
    {
        return $this->getData(self::OFFSET);
    }

    public function getTotalBatches()
    {
        return $this->getData(self::TOTAL_BATCHES);
    }

    public function getBatchNumber()
    {
        return $this->getData(self::BATCH_NUMBER);
    }

    public function getSent()
    {
        return $this->getData(self::SENT);
    }

    public function setId($id)
    {
        $this->setData(self::ENTITY_ID, $id);
        return $this;
    }

    public function setStoreId($storeId)
    {
        $this->setData(self::STORE_ID, $storeId);
        return $this;
    }

    public function setToSend($toSend)
    {
        $this->setData(self::TO_SEND);
        return $this;
    }

    public function setOffset($offset)
    {
        $this->setData(self::OFFSET, $offset);
        return $this;
    }

    public function setTotalBatches($totalBatches)
    {
        $this->setData(self::TOTAL_BATCHES, $totalBatches);
        return $this;
    }

    public function setBatchNumber($batchNumber)
    {
        $this->setData(self::BATCH_NUMBER, $batchNumber);
        return $this;
    }

    public function setSent($sent)
    {
        $this->setData(self::SENT, $sent);
        return $this;
    }

    public function isProcessing()
    {
        return ($this->getSent() == 1);
    }

    public function isSent()
    {
        return ($this->getSent() == 2);
    }

    public function getNext()
    {
        $pushCollection = $this->getCollection();
        $pushCollection->addFieldToFilter('sent', 0);

        $pushCollection->getSelect()->limit(1);
        return $pushCollection->getLastItem();
    }

    public function getAbsoluteUrl()
    {
        $storeUrl = $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );

        if (stripos($storeUrl, 'index.php') !== false) {
            $storeUrl .= '/autocompleteplus/products/pushbulk/pushid/' . $this->getId();
        } else {
            $storeUrl .= '/index.php/autocompleteplus/products/pushbulk/pushid/' . $this->getId();
        }
        return $storeUrl;
    }
}
