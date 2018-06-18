<?php
/**
 * ChecksumRepositoryInterface File
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
 * ChecksumRepositoryInterface
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
interface ChecksumRepositoryInterface
{
    /**
     * Save Checksum.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface $checksum
     * @return \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\ChecksumInterface $checksum);

    /**
     * Retrieve Checksum.
     *
     * @param int $checksumId
     * @return \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($checksumId);

    /**
     * Retrieve Checksums matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Autocompleteplus\Autosuggest\Api\Data\ChecksumSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Checksum.
     *
     * @param \Autocompleteplus\Autosuggest\Api\Data\ChecksumInterface $checksum
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\ChecksumInterface $checksum);

    /**
     * Delete Checksum by ID.
     *
     * @param int $checksumId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($checksumId);
}
