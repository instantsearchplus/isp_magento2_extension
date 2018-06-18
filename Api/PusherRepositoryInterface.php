<?php
/**
 * PusherRepositoryInterface File
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
 * PusherRepositoryInterface
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
interface PusherRepositoryInterface
{
    /**
     * Save Pusher.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\PusherInterface $pusher
     * @return \Autocompleteplus\Autosuggest\Api\Data\PusherInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\PusherInterface $pusher);

    /**
     * Retrieve Pusher.
     *
     * @param int $pusherId
     * @return \Autocompleteplus\Autosuggest\Api\Data\PusherInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($pusherId);

    /**
     * Retrieve Pushers matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Autocompleteplus\Autosuggest\Api\Data\PusherSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Pusher.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\PusherInterface $pusher
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\PusherInterface $pusher);

    /**
     * Delete Pusher by ID.
     *
     * @param int $pusherId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($pusherId);
}
