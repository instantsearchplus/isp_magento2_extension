<?php
/**
 * ProductImport File
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
 * @copyright 2017 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * ProductImport
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
 * @copyright 2017 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */
class ProductImport implements ObserverInterface
{
    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Batches
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data
     */
    protected $_dataSourceModel;

    /**
     * @param \Autocompleteplus\Autosuggest\Helper\Batches $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
    ) {
        $this->helper = $helper;
        $this->date = $date;
        $this->_storeManager = $storeManager;
        $this->_dataSourceModel = $importData;
    }

    /**
     * Update products
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        // Suppress Price plugin cascade during the import; flag stays on for the
        // rest of the request (monotonic by design in this module).
        $this->helper->setPluginDisabled(true);
        $to_update = [];

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $skus = [];
            foreach ($bunch as $itemArray) {
                if (!empty($itemArray['sku'])) {
                    $skus[] = $itemArray['sku'];
                }
            }
            if (empty($skus)) {
                continue;
            }
            $skuToId = $this->helper->getProductIdsBySkus($skus);
            if (empty($skuToId)) {
                continue;
            }

            $productIds = array_values($skuToId);
            $productStoresMap = $this->helper->getProductStoresByIds($productIds);
            $parentIdsMap = $this->helper->getParentIdsByChildren($productIds);
            $dt = $this->date->gmtTimestamp();

            foreach ($bunch as $itemArray) {
                $sku = $itemArray['sku'] ?? null;
                if (!$sku || !isset($skuToId[$sku])) {
                    continue;
                }
                $productId = $skuToId[$sku];
                $productStores = $productStoresMap[$productId] ?? [$storeId];

                if (array_key_exists('status', $itemArray) && $itemArray['status'] == '2') {
                    foreach ($productStores as $productStoreId) {
                        $to_update[] = [
                            'product_id' => $productId,
                            'store_id'   => (int)$productStoreId,
                            'update_date'=> $dt,
                            'action'     => 'remove',
                            'sku'        => $sku,
                        ];
                    }
                    continue;
                }

                $parentProducts = $parentIdsMap[$productId] ?? [];
                foreach ($productStores as $productStoreId) {
                    $to_update[] = [
                        'product_id' => $productId,
                        'store_id'   => (int)$productStoreId,
                        'update_date'=> $dt,
                        'action'     => 'update',
                        'sku'        => 'ISP_NO_SKU'
                    ];
                    foreach ($parentProducts as $parentId) {
                        $to_update[] = [
                            'product_id' => $parentId,
                            'store_id'   => (int)$productStoreId,
                            'update_date'=> $dt,
                            'action'     => 'update',
                            'sku'        => 'ISP_NO_SKU'
                        ];
                    }
                }
            }
        }

        if (count($to_update) > 0) {
            $this->helper->upsertData($to_update);
        }

        return $this;
    }
}
