<?php
/**
 * NotificationRepositoryInterface File
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

namespace Autocompleteplus\Autosuggest\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * NotificationRepositoryInterface
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
interface NotificationRepositoryInterface
{
    /**
     * Save Notification.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\NotificationInterface $notification
     * @return \Autocompleteplus\Autosuggest\Api\Data\NotificationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\NotificationInterface $notification);

    /**
     * Retrieve Notification.
     *
     * @param int $notificationId
     * @return \Autocompleteplus\Autosuggest\Api\Data\NotificationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($notificationId);

    /**
     * Retrieve Notifications matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Autocompleteplus\Autosuggest\Api\Data\NotificationSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Notification.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\NotificationInterface $notification
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\NotificationInterface $notification);

    /**
     * Delete Notification by ID.
     *
     * @param int $notificationId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($notificationId);
}
