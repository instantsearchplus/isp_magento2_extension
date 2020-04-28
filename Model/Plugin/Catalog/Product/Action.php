<?php
/**
 * Action.php
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
 * @copyright 2020 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Model\Plugin\Catalog\Product;

/**
 * Class Action
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
 * @copyright Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class Action
{
    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Batches
     */
    protected $batchesHelper;

    /**
     * Action constructor.
     * @param \Autocompleteplus\Autosuggest\Helper\Batches $batchesHelper
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $batchesHelper
    ) {
        $this->batchesHelper = $batchesHelper;
    }

    public function aroundUpdateAttributes($subject, $proceed, $entityIds, $attrData, $storeId) {
        $this->batchesHelper->writeMassProductsUpdate($entityIds, $storeId);
        return $proceed($entityIds, $attrData, $storeId);
    }
}