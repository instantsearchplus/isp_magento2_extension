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

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Model\Product\Visibility $productVisibility
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManagerInterface;
        $this->productVisibility = $productVisibility;
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $price_index_table_name = $resource->getTableName('catalog_product_index_price');
        $eav_table_name = $resource->getTableName('eav_attribute');
        $entity_int_table_name = $resource->getTableName('catalog_product_entity_int');
        $cataloginventory_stock_status_table_name = $resource->getTableName('cataloginventory_stock_status');
        $cataloginventory_stock_item = $resource->getTableName('cataloginventory_stock_item');
        $catalog_product_super_link = $resource->getTableName('catalog_product_super_link');

        $sql = $connection->select()
            ->from($eav_table_name, 'attribute_id')
            ->where(sprintf('%s.attribute_code = ?', $eav_table_name), 'visibility')
            ->limitPage(1, 1);

        $visibility_attribute_id = $connection->fetchOne($sql);

        $connection->query("SET sql_mode='NO_ENGINE_SUBSTITUTION';");

        $page = (int)ceil((float)$startInd/$count);
        $store = $this->storeManager->getStore($store);
        $website_id = $store->getWebsiteId();

        $entity_id_col_name = 'entity_id';
        $column_info = $connection->fetchAll(sprintf('SHOW COLUMNS FROM `%s` LIKE "%s";', $entity_int_table_name, $entity_id_col_name));

        if (count($column_info) == 0) {
            $entity_id_col_name = 'row_id';
        }

        $fields = [
            sprintf("%s.entity_id", $price_index_table_name),
            sprintf("%s.final_price", $price_index_table_name)
        ];

        $sql = $connection->select()
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
            ->where(sprintf('%s.value IN (?)', $entity_int_table_name), [3,4])
            ->where(sprintf('%s.customer_group_id = ?', $price_index_table_name), $customer_group)
            ->where(sprintf('%s.website_id = ?', $price_index_table_name), $website_id)
            ->where(new \Zend_Db_Expr(
                sprintf("((%s.qty > 0) OR (%s.stock_status = 1))", $cataloginventory_stock_item, $cataloginventory_stock_status_table_name)
            ));

        if ($product_id > 0) {
            $sql->where(sprintf("`%s`.`entity_id` = ?", $price_index_table_name), $product_id);
        }

        $sql->group(sprintf('%s.entity_id', $price_index_table_name))
            ->limitPage($page, $count);

        $results = $connection->fetchAll($sql);
        $result = [];
        foreach ($results as $res) {
            $result[$res['entity_id']] = [
                'id' => $res['entity_id'],
                'final_price' => $res['final_price']
            ];
        }
        return $result;
    }
}
