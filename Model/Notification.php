<?php
/**
 * Notification File
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

use Autocompleteplus\Autosuggest\Api\Data\NotificationInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Notification
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
class Notification extends \Magento\Framework\Model\AbstractModel implements NotificationInterface, IdentityInterface
{
    /**
     * Autosuggest notification cache tag
     */
    const CACHE_TAG = 'autosuggest_notification';

    /**
     * @var string
     */
    protected $_cacheTag = 'autosuggest_notification';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'autosuggest_notification';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Autocompleteplus\Autosuggest\Model\ResourceModel\Notification');
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
        return parent::getId(); // TODO: Change the autogenerated stub
    }

    public function getType()
    {
        // TODO: Implement getType() method.
    }

    public function getSubject()
    {
        // TODO: Implement getSubject() method.
    }

    public function getMessage()
    {
        // TODO: Implement getMessage() method.
    }

    public function getTimestamp()
    {
        // TODO: Implement getTimestamp() method.
    }

    public function isActive()
    {
        // TODO: Implement isActive() method.
    }

    public function setId($value)
    {
        return parent::setId($value); // TODO: Change the autogenerated stub
    }

    public function setType($type)
    {
        // TODO: Implement setType() method.
    }

    public function setSubject($subject)
    {
        // TODO: Implement setSubject() method.
    }

    public function setMessage($message)
    {
        // TODO: Implement setMessage() method.
    }

    public function setTimestamp($timestamp)
    {
        // TODO: Implement setTimestamp() method.
    }

    public function setIsActive($isActive)
    {
        // TODO: Implement setIsActive() method.
    }
}
