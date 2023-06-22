<?php
/**
 * Report File
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

namespace Autocompleteplus\Autosuggest\Helper\Catalog;

/**
 * Report
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
class Report extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    protected $connection;

    protected $resource;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->date = $date;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManagerInterface;
        $this->productVisibility = $productVisibility;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        parent::__construct($context);
    }

    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    public function getCurrentStoreId()
    {
        if ($this->storeId) {
            return $this->storeId;
        }
        return $this->storeManager->getStore()->getId();
    }

    public function getProductCollection()
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($this->getCurrentStoreId());
        return $productCollection;
    }

    public function getEnabledProducts()
    {
        return $this->getProductCollection()->addAttributeToFilter(
            'status',
            ['eq'  =>  \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED]
        );
    }

    public function getDisabledProducts()
    {
        return $this->getProductCollection()->addAttributeToFilter(
            'status',
            ['eq'  =>  \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED]
        );
    }

    public function getVisibleInCatalogProducts()
    {
        return $this->getProductCollection()
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds());
    }

    public function getVisibleInSearchProducts()
    {
        return $this->getEnabledProducts()
            ->setVisibility($this->productVisibility->getVisibleInSearchIds());
    }

    public function getDisabledProductsCount()
    {
        $collection = $this->getDisabledProducts();
        return $collection->getSize();
    }

    public function getEnabledProductsCount()
    {
        $collection = $this->getEnabledProducts();
        return $collection->getSize();
    }

    public function getSearchableProductsCount()
    {
        $collection = $this->getVisibleInSearchProducts();
        return $collection->getSize();
    }

    public function getSearchableProductsIds()
    {
        $collection = $this->getVisibleInSearchProducts();
        $ids = [];
        foreach ($collection as $product) {
            $ids[] = [
                'id' => $product->getID(),
                'sku' => $product->getSku()
            ];
        }
        return $ids;
    }

    public function getSecondarySearchableProductsCount()
    {
        $collection = $this->getEnabledProducts();
        $collection->addAttributeToFilter(
            'visibility', [
                ['finset'  =>  3],
                ['finset'  =>  4]
            ]
        );
        return $collection->getSize();
    }

    /**
     * @param  $store
     * @param  $customer_group
     * @param  $count
     * @param  $startInd
     * @return array
     * @throws Varien_Exception
     */
    public function getPricesFromIndex($store, $customer_group, $count, $startInd, $product_id)
    {
        $autosuggest_price_table = $this->resource->getTableName('autosuggest_price');
        $autosuggest_batch_table = $this->resource->getTableName('autosuggest_batch');

        $page = (int)ceil((float)$startInd/$count);
        $store = $this->storeManager->getStore($store);
        $website_id = $store->getWebsiteId();
        $website = $this->storeManager->getWebsite($website_id);
        $website_stores = $this->storeManager->getStores();
        $website_stores_ids = [];

        foreach ($website_stores as $w_st) {
            $website_stores_ids[] = $w_st->getStoreId();
        }

        try {
            list($price_index_result, $product_ids) = $this->getPriceIndexRows($customer_group, $count, $website_id, $page, $product_id);
        } catch (\Exception $e) {
            return [];
        }

        if ($product_id != 0) {
            return $price_index_result;
        }

        if ($page == 0) {
            $this->connection->query(sprintf("UPDATE %s SET is_updated=0 WHERE website_id = %s", $autosuggest_price_table, $website_id));
        }

        $sql = $this->connection->select()
            ->from($autosuggest_price_table, '*')
            ->where(sprintf('%s.product_id IN (?)', $autosuggest_price_table), $product_ids)
            ->where(sprintf('%s.website_id = ?', $autosuggest_price_table), $website_id)
            ->limitPage(1, $count);

        /**
         * in this array will be either products with changed prices
         * or new products that are not in autosuggest_price table
         */
        $to_updateIds = [];
        $autosuggest_price_results = $this->connection->fetchAll($sql);
        foreach ($autosuggest_price_results as $res) {
            unset($product_ids[array_search($res['product_id'], $product_ids)]);
            if ($price_index_result[$res['product_id']]['final_price'] != $res['final_price']) {
                $to_updateIds[] = $res['product_id'];
            }
        }

        if (count($product_ids) > 0) {
            $to_updateIds = array_merge($to_updateIds, $product_ids);
        }

        /**
         * preparing data for updating batches table with changed/new product ids
         */
        foreach ($website_stores_ids as $w_st_id) {
            $batches_data = [];
            foreach ($to_updateIds as $to_upd_prod) {
                $batches_data[] = [
                    'store_id' => (int)$w_st_id,
                    'product_id' => (int)$to_upd_prod,
                    'update_date' => (int)$this->date->gmtTimestamp(),
                    'action' => 'update'
                ];
            }
            if (count($batches_data) > 0) {
                $this->connection->insertOnDuplicate($autosuggest_batch_table, $batches_data);
            }
        }

        /**
         * preparing data for updating autosuggest_price table
         */
        $prices_data = [];
        foreach ($price_index_result as $prod_data) {
            $prices_data[] = [
                'website_id' => (int)$website_id,
                'product_id' => (int)$prod_data['id'],
                'final_price' => (float)$prod_data['final_price'],
                'update_date' => (int)$this->date->gmtTimestamp(),
                'is_updated' => 1
            ];
        }
        if (count($prices_data) > 0) {
            $this->connection->insertOnDuplicate($autosuggest_price_table, $prices_data);
        }

        /**
         * on the last page
         * getting items that were not found in price_index table, probably deleted
         */
        if (count($price_index_result) < $count) {
            $sql = $this->connection->select()
                ->from($autosuggest_price_table, '*')
                ->where(sprintf('%s.is_updated = 0', $autosuggest_price_table))
                ->where(sprintf('%s.website_id = ?', $autosuggest_price_table), $website_id)
                ->limitPage(1, $count);

            $results = $this->connection->fetchAll($sql);

            /**
             * preparing data for updating batches table
             */
            foreach ($website_stores_ids as $w_st_id) {
                $batches_data = [];
                foreach ($results as $to_upd_prod) {
                    $batches_data[] = [
                        'store_id' => (int)$w_st_id,
                        'product_id' => (int)$to_upd_prod['product_id'],
                        'update_date' => (int)$this->date->gmtTimestamp(),
                        'action' => 'update'
                    ];
                }
                if (count($batches_data) > 0) {
                    $this->connection->insertOnDuplicate($autosuggest_batch_table, $batches_data);
                }
            }
        }

        return $price_index_result;
    }

    /**
     * @param $customer_group
     * @param $count
     * @param $product_id
     * @param $connection
     * @param $entity_int_table_name
     * @param $price_index_table_name
     * @param $cataloginventory_stock_item
     * @param $catalog_product_super_link
     * @param $cataloginventory_stock_status_table_name
     * @param $visibility_attribute_id
     * @param $website_id
     * @param $status_attribute_id
     * @param $page
     * @return array
     */
    protected function getPriceIndexRows($customer_group, $count, $website_id, $page, $product_id)
    {
        $product_ids = [];
        $price_index_table_name = $this->resource->getTableName('catalog_product_index_price');
        $eav_table_name = $this->resource->getTableName('eav_attribute');
        $entity_int_table_name = $this->resource->getTableName('catalog_product_entity_int');
        $cataloginventory_stock_status_table_name = $this->resource->getTableName('cataloginventory_stock_status');
        $cataloginventory_stock_item = $this->resource->getTableName('cataloginventory_stock_item');
        $catalog_product_super_link = $this->resource->getTableName('catalog_product_super_link');

        $sql = $this->connection->select()
            ->from($eav_table_name, 'attribute_id')
            ->where(sprintf('%s.attribute_code = ?', $eav_table_name), 'visibility')
            ->limitPage(1, 1);

        $visibility_attribute_id = $this->connection->fetchOne($sql);

        $sql = $this->connection->select()
            ->from($eav_table_name, 'attribute_id')
            ->where(sprintf('%s.attribute_code = ?', $eav_table_name), 'status')
            ->limitPage(1, 1);

        $status_attribute_id = $this->connection->fetchOne($sql);

        $this->connection->query("SET sql_mode='NO_ENGINE_SUBSTITUTION';");

        $entity_id_col_name = 'entity_id';
        $column_info = $this->connection->fetchAll(sprintf('SHOW COLUMNS FROM `%s` LIKE "%s";', $entity_int_table_name, $entity_id_col_name));

        if (count($column_info) == 0) {
            $entity_id_col_name = 'row_id';
        }

        $fields = [
            sprintf("%s.entity_id", $price_index_table_name),
            sprintf("%s.final_price", $price_index_table_name)
        ];

        if ($product_id != 0) {
            $fields[] = sprintf("%s.min_price", $price_index_table_name);
            $fields[] = sprintf("%s.max_price", $price_index_table_name);
            $fields[] = sprintf("%s.price", $price_index_table_name);
        }

        $sql = $this->connection->select()
            ->from($entity_int_table_name, [])
            ->join(
                $price_index_table_name,
                sprintf(
                    "%s.%s = %s.entity_id",
                    $entity_int_table_name,
                    $entity_id_col_name,
                    $price_index_table_name
                ),
                $fields
            )
            ->join(
                $cataloginventory_stock_item,
                sprintf(
                    "%s.product_id = %s.entity_id",
                    $cataloginventory_stock_item,
                    $price_index_table_name
                ),
                []
            )
            ->joinLeft(
                $catalog_product_super_link,
                sprintf(
                    "%s.entity_id = %s.parent_id",
                    $price_index_table_name,
                    $catalog_product_super_link
                ),
                []
            )
            ->joinLeft(
                $cataloginventory_stock_status_table_name,
                sprintf(
                    "%s.product_id = %s.product_id",
                    $cataloginventory_stock_status_table_name,
                    $catalog_product_super_link
                ),
                []
            )
            ->where(sprintf('%s.attribute_id = ?', $entity_int_table_name), $visibility_attribute_id)
            ->where(sprintf('%s.value IN (?)', $entity_int_table_name), [3, 4])
            ->where(sprintf('%s.customer_group_id = ?', $price_index_table_name), $customer_group)
            ->where(sprintf('%s.website_id = ?', $price_index_table_name), $website_id)
            ->where(
                sprintf("((%s.qty > 0) OR (%s.stock_status = 1 AND %s.product_id NOT IN (SELECT %s FROM %s WHERE attribute_id = %d AND value = 2)))",
                    $cataloginventory_stock_item,
                    $cataloginventory_stock_status_table_name,
                    $cataloginventory_stock_status_table_name,
                    $entity_id_col_name,
                    $entity_int_table_name,
                    $status_attribute_id
                )
            );

        if ($product_id > 0) {
            $sql->where(sprintf("`%s`.`entity_id` = ?", $price_index_table_name), $product_id);
        }

        $sql->group(sprintf('%s.entity_id', $price_index_table_name))
            ->limitPage($page, $count);

        $price_index_results = $this->connection->fetchAll($sql);
        $price_index_rows = [];
        foreach ($price_index_results as $res) {
            $price_index_rows[$res['entity_id']] = [
                'id' => $res['entity_id'],
                'final_price' => $res['final_price']
            ];

            if ($product_id != 0) {
                $price_index_rows[$res['entity_id']]['min_price'] = $res['min_price'];
                $price_index_rows[$res['entity_id']]['max_price'] = $res['max_price'];
                $price_index_rows[$res['entity_id']]['price'] = $res['price'];
            }

            $product_ids[] = $res['entity_id'];
        }
        return array($price_index_rows, $product_ids);
    }
}
