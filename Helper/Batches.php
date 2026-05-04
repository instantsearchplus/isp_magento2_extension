<?php
/**
 * Batches.php
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

namespace Autocompleteplus\Autosuggest\Helper;

/**
 * Class Batches
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
class Batches extends \Magento\Framework\App\Helper\AbstractHelper
{
    const AUTOSUGGEST_BATCH_TABLE_NAME = 'autosuggest_batch';
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory
     */
    protected $batchCollectionFactory;

    protected $productModel;

    protected $batchModel;

    protected $_resourceConnection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    protected $_storeManager;

    protected $pricePluginDisabled = false;

    protected $dbConnection;

    const MULTIPLE_INSERT_SIZE = 200;

    public function __construct(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory $batchCollectionFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Autocompleteplus\Autosuggest\Model\Batch $batchModel
    ) {
        $this->date = $date;
        $this->configurable = $configurable;
        $this->logger = $logger;
        $this->batchCollectionFactory = $batchCollectionFactory;
        $this->productModel = $productModel;
        $this->batchModel = $batchModel;
        $this->_resourceConnection = $resourceConnection;
        $this->_storeManager = $storeManager;
        $this->dbConnection = $this->_resourceConnection->getConnection();
    }

    public function setPluginDisabled($disabled)
    {
        $this->pricePluginDisabled = $disabled;
    }

    public function getPluginDisabled()
    {
        return $this->pricePluginDisabled;
    }

    /**
     * @param $product
     * @param $productId
     * @param $storeId
     * @param $dt
     * @param $sku
     */
    public function writeProductUpdate($product, $productId, $storeId, $dt, $sku)
    {
        try {
            try {
                if (!$product) {
                    $product = $this->productModel->load($productId);
                }

                $productStores = ($storeId == 0 && method_exists($product, 'getStoreIds')) ? $product->getStoreIds() : [$storeId];
            } catch (\Exception $e) {

                $this->logger->critical($e);
                $productStores = [$storeId];
            }
            //preventing reindex plugin to run
            $this->setPluginDisabled(true);

            $simpleProducts = [];
            if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
                $simpleProducts = $this->configurable->getParentIdsByChild($product->getId());
            }
            foreach ($productStores as $productStore) {
                $data = [
                    'product_id'=> $productId,
                    'store_id'=> $productStore,
                    'update_date'=> $dt,
                    'action'=> 'update',
                    'sku'=> 'ISP_NO_SKU'
                ];
                $this->upsertData($data);

                if (is_array($simpleProducts) && count($simpleProducts) > 0) {
                    foreach ($simpleProducts as $configurableProduct) {
                        $data = [
                            'product_id'=> $configurableProduct,
                            'store_id'=> $productStore,
                            'update_date'=> $dt,
                            'action'=> 'update',
                            'sku'=> 'ISP_NO_SKU'
                        ];
                        if (count($data) > 0) {
                            $this->upsertData($data);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param $sku
     * @param $productId
     * @param $storeId
     * @param null      $product
     */
    public function writeProductDeletion($sku, $productId, $storeId, $dt, $product = null)
    {
        try {
            if (!$product) {
                $product = $this->productModel->load($productId);
            }

            $productStores = ($storeId == 0 && method_exists($product, 'getStoreIds')) ?
                $product->getStoreIds() : [$storeId];
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $productStores = [$storeId];
        }
        //preventing reindex plugin to run
        $this->setPluginDisabled(true);

        $this->removeProductForEachStore($sku, $productId, $dt, $productStores);
    }

    /**
     * @param $sku
     * @param $productId
     * @param $storeId
     * @param null      $product
     */
    public function writeProductDeletionLight($sku, $productId, $storeId, $dt, $productStores = null)
    {
        if (!$productStores) {
            $productStores = [$storeId];
        }
        //preventing reindex plugin to run
        $this->setPluginDisabled(true);

        $this->removeProductForEachStore($sku, $productId, $dt, $productStores);
    }

    /**
     * @param $sku
     * @param $productId
     * @param $dt
     * @param $productStores
     */
    protected function removeProductForEachStore($sku, $productId, $dt, $productStores)
    {
        try {
            if ($sku == null) {
                $sku = 'dummy_sku';
            }
            foreach ($productStores as $productStore) {
                $data = [
                    'product_id' => $productId,
                    'store_id' => $productStore,
                    'update_date' => $dt,
                    'action' => 'remove',
                    'sku' => $sku
                ];
                if (count($data) > 0) {
                    $this->upsertData($data);
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param $product_ids
     * @param $rows
     */
    public function writeMassProductsUpdate($products_ids, $store_id)
    {
        //preventing reindex plugin to run
        $this->setPluginDisabled(true);

        $counter = 0;
        $data = [];
        foreach ($products_ids as $p_id) {
            $counter++;
            $data[] = [
                'store_id' => (int)$store_id,
                'product_id' => (int)$p_id,
                'update_date' => (int)$this->date->gmtTimestamp() + $counter,
                'action' => 'update',
                'sku' => 'ISP_NO_SKU'
            ];
            if ($counter == self::MULTIPLE_INSERT_SIZE) {
                $this->logger->info('executed multiple insert of ' . count($data));
                $counter = 0;
                if (count($data) > 0) {
                    $this->upsertData($data);
                    $data = [];
                }
            }
        }

        if (count($data) > 0) {
            $this->upsertData($data);
        }
    }

    /**
     * Batch product -> store resolution via catalog_product_website joined with
     * the StoreManager website map. Products with no catalog_product_website row
     * are omitted from the result.
     *
     * @return array<int, int[]>  [product_id => store_ids[]]
     */
    public function getProductStoresByIds(array $product_ids): array
    {
        if (empty($product_ids)) {
            return [];
        }

        $table_name = $this->_resourceConnection->getTableName('catalog_product_website');
        $select = $this->dbConnection->select()
            ->from($table_name, ['product_id', 'website_id'])
            ->where('product_id IN (?)', $product_ids);
        $rows = $this->dbConnection->fetchAll($select);

        $websiteStoreMap = [];
        foreach ($this->_storeManager->getWebsites() as $website) {
            $websiteStoreMap[(int)$website->getId()] = $website->getStoreIds();
        }

        $result = [];
        foreach ($rows as $row) {
            $pid = (int)$row['product_id'];
            $wid = (int)$row['website_id'];
            if (!isset($websiteStoreMap[$wid])) {
                continue;
            }
            foreach ($websiteStoreMap[$wid] as $sid) {
                $result[$pid][(int)$sid] = (int)$sid;
            }
        }

        foreach ($result as $pid => $stores) {
            $result[$pid] = array_values($stores);
        }
        return $result;
    }

    /**
     * Given a list of product IDs, returns a map grouping those product IDs by
     * each store they're assigned to (via catalog_product_website).
     *
     * @return array<int, int[]>  [store_id => product_ids[]]
     */
    public function groupProductIdsByStore(array $product_ids): array
    {
        $map = $this->getProductStoresByIds($product_ids);
        $result = [];
        foreach ($map as $product_id => $store_ids) {
            foreach ($store_ids as $store_id) {
                $result[$store_id][] = $product_id;
            }
        }
        return $result;
    }

    /**
     * Batch SKU -> entity_id lookup. Missing SKUs are omitted from the result.
     *
     * @return array<string, int>  [sku => product_id]
     */
    public function getProductIdsBySkus(array $skus): array
    {
        if (empty($skus)) {
            return [];
        }
        $table = $this->_resourceConnection->getTableName('catalog_product_entity');
        $select = $this->dbConnection->select()
            ->from($table, ['sku', 'entity_id'])
            ->where('sku IN (?)', $skus);
        $result = [];
        foreach ($this->dbConnection->fetchAll($select) as $row) {
            $result[$row['sku']] = (int)$row['entity_id'];
        }
        return $result;
    }

    /**
     * Batch child -> parent lookup for configurable products via catalog_product_super_link.
     * Children with no configurable parent are omitted.
     *
     * @return array<int, int[]>  [child_id => parent_ids[]]
     */
    public function getParentIdsByChildren(array $childIds): array
    {
        if (empty($childIds)) {
            return [];
        }
        $table = $this->_resourceConnection->getTableName('catalog_product_super_link');
        $select = $this->dbConnection->select()
            ->from($table, ['product_id', 'parent_id'])
            ->where('product_id IN (?)', $childIds);
        $result = [];
        foreach ($this->dbConnection->fetchAll($select) as $row) {
            $result[(int)$row['product_id']][] = (int)$row['parent_id'];
        }
        return $result;
    }

    /**
     * @param $table_name
     * @param $data
     */
    public function upsertData($data, $table_name=null)
    {
        if (!$table_name) {
            $table_name = self::AUTOSUGGEST_BATCH_TABLE_NAME;
        }
        $table_name = $this->_resourceConnection->getTableName($table_name);
        try {
            $this->dbConnection->insertOnDuplicate($table_name, $data);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $this->logger->info('executed multiple insert of ' . count($data));
    }
}
