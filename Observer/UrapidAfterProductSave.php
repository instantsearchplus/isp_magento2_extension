<?php
/**
 * UrapidAfterProductSave.php
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
 * @copyright 2019 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class UrapidAfterProductSave
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
class UrapidAfterProductSave implements ObserverInterface
{
    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Batches
     */
    protected $helper;

    /**
     * @param \Autocompleteplus\Autosuggest\Helper\Batches $helper
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $helper
    ) {
        $this->helper = $helper;
    }


    /**
     * Update products
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $vars = $observer->getEvent()->getVars();
        $skus = $vars['skus'];
        $profile = $vars['profile'];
        $store_id = (int)$profile->getStoreId();
        $product_ids = [];
        foreach ($skus as $sku => $productId) {
            $product_ids[] = (int)$productId;
        }

        if (empty($product_ids)) {
            return $this;
        }

        if ($store_id === 0) {
            foreach ($this->helper->groupProductIdsByStore($product_ids) as $storeId => $ids) {
                $this->helper->writeMassProductsUpdate($ids, $storeId);
            }
        } else {
            $this->helper->writeMassProductsUpdate($product_ids, $store_id);
        }

        return $this;
    }
}
