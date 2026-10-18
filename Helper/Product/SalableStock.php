<?php
/**
 * SalableStock File
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package   Instantsearchplus
 * @author    Fast Simon <info@instantsearchplus.com>
 * @copyright 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Helper\Product;

use Magento\Framework\App\ObjectManager;

/**
 * Reads what the storefront can sell from Magento's multi-source inventory (MSI).
 *
 * Legacy cataloginventory_stock_item holds the Default source only, so on a website assigned to any
 * other stock it understates stock. On the Default Stock MSI reads that same legacy data, so useMsi()
 * is false there and callers keep their legacy reads unchanged.
 *
 * The MSI services are resolved lazily by name: composer declares no MSI dependency, and Magento 2.2
 * has none. Every MSI call catches \Throwable, because a missing preference throws an \Error that
 * would escape the feed's catch (\Exception) and fail the whole request.
 */
class SalableStock
{
    const MSI_MODULES = [
        'Magento_InventorySalesApi',
        'Magento_InventorySales',
        'Magento_InventoryCatalogApi',
        'Magento_InventoryCatalog',
    ];

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array store id => stock id, or null when MSI does not apply
     */
    protected $stockIds = [];

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->moduleManager = $moduleManager;
        $this->storeManager = $storeManager;
    }

    /**
     * @param int $storeId
     * @return bool true when the store's website sells from a stock other than the Default Stock
     */
    public function useMsi(int $storeId): bool
    {
        return $this->getStockId($storeId) !== null;
    }

    /**
     * @param string $sku
     * @param int $storeId
     * @return bool
     */
    public function isSalable(string $sku, int $storeId): bool
    {
        $stockId = $this->getStockId($storeId);
        if ($stockId === null) {
            return false;
        }
        try {
            return (bool)$this->get('Magento\InventorySalesApi\Api\IsProductSalableInterface')
                ->execute($sku, $stockId);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param string $sku
     * @param int $storeId
     * @return float|null the salable qty, or null when it is not positive or unreadable. Unfloored, so
     *     a fractional qty still reads as in stock, as the legacy qty check does; callers floor it
     *     where they emit an integer qty.
     */
    public function getSalableQty(string $sku, int $storeId)
    {
        $stockId = $this->getStockId($storeId);
        if ($stockId === null) {
            return null;
        }
        try {
            $qty = (float)$this->get('Magento\InventorySalesApi\Api\GetProductSalableQtyInterface')
                ->execute($sku, $stockId);
        } catch (\Throwable $e) {
            return null;
        }
        return $qty > 0 ? $qty : null;
    }

    /**
     * @param int $storeId
     * @return int|null the website's stock id, or null when MSI is absent or the stock is the Default Stock
     */
    protected function getStockId(int $storeId)
    {
        if (!array_key_exists($storeId, $this->stockIds)) {
            $this->stockIds[$storeId] = $this->resolveStockId($storeId);
        }
        return $this->stockIds[$storeId];
    }

    /**
     * @param int $storeId
     * @return int|null
     */
    protected function resolveStockId(int $storeId)
    {
        try {
            foreach (self::MSI_MODULES as $module) {
                if (!$this->moduleManager->isEnabled($module)) {
                    return null;
                }
            }
            $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
            $stockId = (int)$this->get('Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface')
                ->execute($websiteId)
                ->getStockId();
            $defaultStockId = (int)$this->get('Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface')
                ->getId();
            return $stockId !== $defaultStockId ? $stockId : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param string $type
     * @return object
     */
    protected function get(string $type)
    {
        return ObjectManager::getInstance()->get($type);
    }
}
