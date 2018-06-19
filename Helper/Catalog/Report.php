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
        return $this->getProductCollection()->addAttributeToFilter('status',
            ['eq'  =>  \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED]);
    }

    public function getDisabledProducts()
    {
        return $this->getProductCollection()->addAttributeToFilter('status',
            ['eq'  =>  \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED]);
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
        $ids = array();
        foreach($collection as $product) {
            $ids[] = array(
                'id' => $product->getID(),
                'sku' => $product->getSku()
            );
        }
        return $ids;
    }

    public function getSecondarySearchableProductsCount()
    {
        $collection = $this->getEnabledProducts();
        $collection->addAttributeToFilter('visibility', [
                ['finset'  =>  3],
                ['finset'  =>  4]
            ]);
        return $collection->getSize();
    }

    /**
     * @param $store
     * @param $customer_group
     * @param $count
     * @param $startInd
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
        $product_entity_table_name = $resource->getTableName('catalog_product_entity');

        $params = array();
        $page = (int)ceil((float)$startInd/$count) ;
        $store = $this->storeManager->getStore($store);
        $website_id = $store->getWebsiteId();

        $fields = array(
            sprintf("%s.attribute_id AS attribute_id", $eav_table_name),
            sprintf("%s.entity_id AS entity_id", $entity_int_table_name),
            sprintf("%s.value AS value", $entity_int_table_name),
            sprintf("%s.attribute_code AS attribute_code", $eav_table_name),
            sprintf("%s.type_id AS type_id", $product_entity_table_name),
            sprintf("%s.*", $price_index_table_name)
        );

        $sql = $connection->select()
            ->from($eav_table_name, $fields)
            ->join($entity_int_table_name, sprintf("%s.attribute_id = %s.attribute_id", $eav_table_name, $entity_int_table_name))
            ->join($price_index_table_name, sprintf("%s.entity_id = %s.entity_id", $entity_int_table_name, $price_index_table_name))
            ->join($product_entity_table_name, sprintf("%s.entity_id = %s.entity_id", $product_entity_table_name, $price_index_table_name))
            ->where(sprintf('%s.attribute_code = ?', $eav_table_name), 'visibility')
            ->where(sprintf('%s.value IN (?)', $entity_int_table_name), array(3,4))
            ->where(sprintf('%s.customer_group_id = ?', $price_index_table_name), $customer_group)
            ->where(sprintf('%s.website_id = ?', $price_index_table_name), $website_id)
            ->limitPage($page, $count);

        if ($product_id > 0) {
            $sql->where(sprintf("`%s`.`entity_id` = ?", $price_index_table_name), $product_id);
        }

        $results = $connection->fetchAll($sql);
        $result = array();
        foreach ($results as $res) {
            $result[$res['entity_id']] = array(
                'id' => $res['entity_id'],
                'final_price' => $res['final_price'],
                'min_price' => $res['min_price'],
                'tier_price' => $res['tier_price'],
                'website_id' => $res['website_id'],
                'customer_group_id' => $res['customer_group_id'],
                'type_id' => $res['type_id']
            );
        }
        return $result;
    }
}
