<?php
/**
 * IspProductSaveLight.php
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

namespace Autocompleteplus\Autosuggest\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class IspProductSaveLight
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
class IspProductSaveLight implements ObserverInterface
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
        $productIds = array_map('intval', (array)$observer->getEvent()->getProductIds());
        if (empty($productIds)) {
            return $this;
        }

        $storesProductsData = $this->helper->groupProductIdsByStore($productIds);

        foreach ($storesProductsData as $storeId => $productIdByStore) {
            $this->helper->writeMassProductsUpdate($productIdByStore, $storeId);
        }

        return $this;
    }
}
