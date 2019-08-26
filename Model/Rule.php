<?php
/**
 * Rule.php
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
 * @copyright 2018 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Model;

class Rule extends \Magento\CatalogRule\Model\Rule
{
    /**
     * Callback function for product matching
     *
     * @param  array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $websites = $this->_getWebsitesMap();
        $results = null;

        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            $valid = (int) $this->getConditions()->validate($product);
            if ($valid) {
                $results = $product;
                break;
            }
        }
        if ($results != null) {
            $this->_productIds[$product->getId()] = [$results];
        }
    }
}
