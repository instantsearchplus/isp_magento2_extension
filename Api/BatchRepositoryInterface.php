<?php
/**
 * BatchRepositoryInterface File
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
use Magento\Framework\App\Router\ActionList\Reader;

/**
 * BatchRepositoryInterface
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
interface BatchRepositoryInterface
{
    /**
     * Save Batch.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\BatchInterface $batch
     * @return \Autocompleteplus\Autosuggest\Api\Data\BatchInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\BatchInterface $batch);

    /**
     * Retrieve Batch.
     *
     * @param int $batchId
     * @return \Autocompleteplus\Autosuggest\Api\Data\BatchInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($batchId);

    /**
     * Retrieve Batchs matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Autocompleteplus\Autosuggest\Api\Data\BatchSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Batch.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\BatchInterface $batch
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\BatchInterface $batch);

    /**
     * Delete Batch by ID.
     *
     * @param int $batchId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($batchId);
}
