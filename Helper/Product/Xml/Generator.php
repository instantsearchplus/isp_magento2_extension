<?php
/**
 * Generator File
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

namespace Autocompleteplus\Autosuggest\Helper\Product\Xml;

use Magento\Framework\App;
use Magento\Framework\UrlInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Framework\App\ObjectManager;


/**
 * Generator
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
class Generator extends \Magento\Framework\App\Helper\AbstractHelper
{
    //<editor-fold desc="Properties">
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\Collection
     */
    protected $batchCollection;

    /**
     * @var \Magento\Review\Model\Review
     */
    protected $reviewModel;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Data
     */
    protected $helper;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Batches
     */
    protected $batchesHelper;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Data
     */
    protected $configurableHelper;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProduct;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Autocompleteplus\Autosuggest\Xml\Generator
     */
    protected $xmlGenerator;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeData
     */
    protected $configurableAttributeData;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var bool
     */
    protected $saleable = false;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attributeCollection;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $image;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $grouped;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory
     */
    protected $batchCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    protected $orderItemCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var int
     */
    protected $count;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var bool
     */
    protected $orders;

    /**
     * @var int
     */
    protected $interval;

    /**
     * @var bool
     */
    protected $minimalOrder;

    /**
     * @var int
     */
    protected $pageNum;

    /**
     * @var array
     */
    protected $_categories;

    /**
     * @var array
     */
    protected $categoryCollection;

    /**
     * @var array caches attribute values for select attribute
     */
    protected $attributesValuesCache;

    /**
     * @var array caches attribute names for each set
     */
    protected $attributesSetsCache;

    protected $productAttributeRepository;

    /**
     * @var \Magento\Eav\Api\AttributeManagementInterface
     */
    protected $productAttributeManagementInterface;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\Options
     */
    protected $attrSetOPtions;

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadataInterface
     */
    protected $entityMetadata;

    /**
     * @var array keeps customer groups names and ids
     */
    protected $_customersGroups;

    protected $productVisibility;

    protected $catalogRuleAffectedProducts;

    protected $catalogFutureRuleAffectedProducts;

    protected $resourceConnection;

    protected $_localeDate;

    protected $productModel;

    protected $ruleCollectionFactory;

    protected $cache;

    protected $rulesCount;

    protected $priceCurrencyInterface;

    protected $stockFactory;

    protected $logger;

    protected $categoriesLocalList;

    protected $ruleModel;

    protected $configurableChildren;

    protected $scheduledUpdatesBuffer;

    protected $productMetadata;

    const ISPKEY = 'ISPKEY_';

    const ActiveRulesCount = 'ActiveRulesCount2';
    const AttributesValuesCache = 'AttributesValuesCache5';
    const AttributesSetsCache = 'AttributesSetsCache5';
    const EAValuesCache = 'EAValuesCache';
    //</editor-fold>

    /**
     * @return array
     */
    public function getAttributesValuesCache()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $attributesValuesCache = $this->cache->load(self::AttributesValuesCache . '_' . $storeId);
        if (!$attributesValuesCache) {
            $this->attributesValuesCache = [];
        } else {
            $this->attributesValuesCache = $this->base64JsonDecode($attributesValuesCache);
        }
        return $this->attributesValuesCache;
    }

    /**
     * @param array $attributesValuesCache
     */
    public function setAttributesValuesCache($attributesValuesCache)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $this->cache->save(
            $this->base64JsonEncode($attributesValuesCache),
            self::AttributesValuesCache . '_' . $storeId,
            ["autocomplete_cache"],
            900
        );
    }

    /**
     * @return array
     */
    public function getAttributesSetsCache()
    {
        $attributesSetsCache = $this->cache->load(self::AttributesSetsCache);
        if (!$attributesSetsCache) {
            $this->attributesSetsCache = [];
        } else {
            $this->attributesSetsCache = $this->base64JsonDecode($attributesSetsCache);
        }
        return $this->attributesSetsCache;
    }

    public function getAttributesSetCachedById($attrSetId)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $attributesSetCached = $this->cache->load(self::AttributesSetsCache . '_' . $attrSetId . '_' . $storeId);
        if (!$attributesSetCached) {
            $attributesSetCached = [];
        } else {
            $attributesSetCached = $this->base64JsonDecode($attributesSetCached);
            $this->attributesSetsCache[$attrSetId] = $attributesSetCached;
        }
        return $attributesSetCached;
    }

    /**
     * @param array $attributesSetsCache
     */
    public function setAttributesSetsCache($attributesSetsCache)
    {
        $storeId = $this->storeManager->getStore()->getId();
        foreach ($attributesSetsCache as $attrSetId => $setData) {
            $this->cache->save(
                $this->base64JsonEncode($setData),
                self::AttributesSetsCache . '_' . $attrSetId . '_' . $storeId,
                ["autocomplete_cache"],
                900
            );
        }
    }

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Batches $batchesHelper,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\ConfigurableProduct\Helper\Data $configurableHelper,
        \Autocompleteplus\Autosuggest\Xml\Generator $xmlGenerator,
        \Magento\Review\Model\Review $reviewModel,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeData $configurableAttributeData,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollection,
        \Magento\Catalog\Helper\Image $image,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  $productCollectionFactory,
        \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory $batchCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\Collection $orderItemCollection,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Eav\Api\AttributeManagementInterface $productAttributeManagementInterface,
        \Magento\Catalog\Model\Product\AttributeSet\Options $attrSetOPtions,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupManager,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $_localeDate,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory  $ruleCollectionFactory,
        \Magento\Framework\App\Cache $cache,
        \Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrencyInterface,
        \Magento\CatalogInventory\Model\Stock\Item $stockFactory,
        \Magento\CatalogRule\Model\ResourceModel\Rule $ruleModel,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/isp_import_debug.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        $this->storeManager = $storeManagerInterface;
        $this->helper = $helper;
        $this->batchesHelper = $batchesHelper;
        $this->productFactory = $productFactory;
        $this->reviewModel = $reviewModel;
        $this->xmlGenerator = $xmlGenerator;
        $this->attributeFactory = $attributeCollection;
        $this->dateTime = $dateTime;
        $this->configurableHelper = $configurableHelper;
        $this->catalogConfig = $catalogConfig;
        $this->catalogProduct = $catalogProduct;
        $this->configurableAttributeData = $configurableAttributeData;
        $this->image = $image;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->configurable = $configurable;
        $this->grouped = $grouped;
        $this->categoryFactory = $categoryFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->batchCollectionFactory = $batchCollectionFactory;
        $this->orderItemCollection = $orderItemCollection;
        $this->date = $date;
        $this->productAttributeManagementInterface = $productAttributeManagementInterface;
        $this->attrSetOPtions = $attrSetOPtions;
        $this->stockRegistry = $stockRegistry;
        $this->productVisibility = $productVisibility;
        $this->resourceConnection = $resourceConnection;
        $this->_localeDate = $_localeDate;
        $this->xmlGenerator->setRootElementName('catalog');
        $this->xmlGenerator->setRootAttributes(
            [
                'version'   =>  $this->helper->getVersion(),
                'magento'   =>  $this->helper->getMagentoVersion()
            ]
        );
        $this->_customersGroups = [];
        $this->catalogRuleAffectedProducts = [];
        $this->catalogFutureRuleAffectedProducts = [];
        $this->resourceConnection = $resourceConnection;
        $this->productModel = $productModel;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->cache = $cache;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->attributesValuesCache = $this->getAttributesValuesCache();
        $this->attributesSetsCache = $this->getAttributesSetsCache();
        $this->priceCurrencyInterface = $priceCurrencyInterface;
        $this->ruleModel = $ruleModel;
        $this->entityMetadata = $metadataPool->getMetadata('Magento\Catalog\Api\Data\ProductInterface');

        foreach ($customerGroupManager->toOptionArray() as $custGr) {
            $this->_customersGroups[$custGr['value']] = $custGr['label'];
        }

        $this->stockFactory = $stockFactory;
        $this->categoriesLocalList = array();
        $this->scheduledUpdatesBuffer = array();
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }

    public function __destruct()
    {
        $this->setAttributesSetsCache($this->attributesSetsCache);
        $this->setAttributesValuesCache($this->attributesValuesCache);
    }

    protected function getActiveRulesCount()
    {
        $activeRulesCount = $this->cache->load(self::ActiveRulesCount);
        if (!$activeRulesCount && $activeRulesCount !== '0') {
            $locTmstmp = $this->_localeDate->scopeTimeStamp($this->getStoreId());
            $dateTs = date('Y-m-d', $locTmstmp);
            $ruleCollection = $this->ruleCollectionFactory->create();
            $ruleCollection->getSelect()
                ->where('to_date BETWEEN ? AND NOW() + INTERVAL 1 MONTH', $dateTs)
                ->orWhere('from_date >= ?', $dateTs)
                ->where('is_active = ?', true);

            $activeRulesCount = $ruleCollection->count();
            $this->cache->save(
                (string)$activeRulesCount,
                self::ActiveRulesCount,
                ["autocomplete_cache"],
                900
            );
        }
        return (int)$activeRulesCount;
    }

    public function getProductCollection($skipNotVisible = true)
    {
        if (!$this->productCollection) {
            $productCollection = $this->productCollectionFactory->create();

            $attributesToSelect = [
                'store_id',
                'name',
                'description',
                'short_description',
                'visibility',
                'thumbnail',
                'image',
                'small_image',
                'url',
                'status',
                'updated_at',
                'price',
                'meta_title',
                'meta_description',
                'special_price',
                'special_from_date',
                'special_to_date',
                'sku',
                'tier_price',
                'msrp'
            ];

            if ($this->helper->canUseProductAttributes()) {
                $customAttributes = $this->getProductAttributes();
                foreach ($customAttributes as $attr) {
                    if (!in_array($attr->getAttributeCode(), $attributesToSelect)) {
                        $attributesToSelect[] = $attr->getAttributeCode();
                    }
                }
            }
            $productCollection->addAttributeToSelect($attributesToSelect);//'*'

            $productCollection->addStoreFilter($this->getStoreId());
            $productCollection->setStoreId($this->getStoreId());
            if ($skipNotVisible) {
                $productCollection->setVisibility($this->productVisibility->getVisibleInSiteIds());
            }
            $this->productCollection = $productCollection;
        }

        return $this->productCollection;
    }

    public function getCategoryCollection()
    {
        if (!$this->categoryCollection) {
            $this->categoryCollection = $this->categoryFactory->create()->getCollection();
            $this->categoryCollection
                ->setStoreId($this->getStoreId())
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('is_active', ['eq' => true]);
        }

        return $this->categoryCollection;
    }

    public function getCategoryCollectionRange($categoriesIds)
    {
        $finalCatsIds = array();
        $categorisList = array();
        foreach ($categoriesIds as $catId) {
            if (array_key_exists($catId, $this->categoriesLocalList)) {
                $categorisList[] = $this->categoriesLocalList[$catId];
            } else {
                $finalCatsIds[] = $catId;
            }
        }
        if (count($finalCatsIds)) {
            $categoryCollectionRange = $this->categoryFactory->create()->getCollection();
            $categoryCollectionRange->setStoreId($this->getStoreId())
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('is_active', ['eq' => true])
                ->addAttributeToFilter('entity_id', ['in' => $finalCatsIds]);
            foreach ($categoryCollectionRange as $cat) {
                $categorisList[] = $cat;
                $this->categoriesLocalList[$cat->getId()] = $cat;
            }
        }
        return $categorisList;
    }

    public function getBatchCollection()
    {
        if (!$this->batchCollection) {
            $batchCollection = $this->batchCollectionFactory->create();
            $this->batchCollection = $batchCollection;
        }

        return $this->batchCollection;
    }

    public function loadProductById($productId, $storeId)
    {
        $product = $this->catalogProductFactory->create();
        $product->setStoreId($storeId);
        $product->load($productId);
        return $product;
    }

    public function getProductAttributes()
    {
        if (!$this->attributeCollection) {
            $this->attributeCollection = $this->attributeFactory->create();
            $entityType = $this->catalogProductFactory->create()->getResource()->getEntityType();
            $entityTypeId = $entityType->getId();
            $this->attributeCollection->setEntityTypeFilter($entityTypeId);
        }

        return $this->attributeCollection;
    }

    public function appendReviews()
    {
        $this->reviewModel->appendSummary($this->getProductCollection());
        return $this;
    }

    public function getRootCategoryId()
    {
        return $this->storeManager->getStore()->getRootCategoryId();
    }

    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    public function getOrdersPerProduct()
    {
        $productIds = implode(',', $this->getProductCollection()->getAllIds());
        $salesOrderItemCollection = $this->orderItemCollection;

        $select = $salesOrderItemCollection->getSelect();
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->columns('SUM(qty_ordered) AS qty_ordered');
        $select->where('store_id = ?', $this->getStoreId());
        $select->where('product_id IN (?)', $productIds);
        $select->where('created_at BETWEEN NOW() - INTERVAL ? MONTH AND NOW()', $this->getInterval());
        $select->group(['product_id']);

        $products = [];

        foreach ($salesOrderItemCollection as $item) {
            $products[$item['product_id']] = (int)$item['qty_ordered'];
        }

        return $products;
    }

    public function getMinimumOrder() {
        if (!$this->minimalOrder) {
            $connection = $this->resourceConnection->getConnection();
            $sales_order = $this->resourceConnection->getTableName('sales_order');
            $sql = $connection->select()
                ->from($sales_order, 'entity_id')
                ->where('store_id = ?', $this->getStoreId())
                ->where('created_at BETWEEN NOW() - INTERVAL ? MONTH AND NOW()', $this->getInterval())
                ->order(array('entity_id'))
                ->limit(1);
            $result = $connection->fetchCol($sql);
            if (count($result) > 0) {
                $this->minimalOrder = $result[0];
            } else {
                $this->minimalOrder = -1;
            }
        }

        return $this->minimalOrder;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    public function setOrders($orders)
    {
        $this->orders = $orders;
        return $this;
    }

    public function setInterval($interval)
    {
        $this->interval = $interval;
        return $this;
    }

    public function setPageNum($pageNum)
    {
        $this->pageNum = $pageNum;
        return $this;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getStoreId()
    {
        return $this->storeId;
    }

    public function getOrders()
    {
        return $this->orders;
    }

    public function getInterval()
    {
        return $this->interval;
    }

    public function getPageNum()
    {
        return $this->pageNum;
    }

    /**
     * Get Allowed Products
     *
     * @return array
     */
    public function getAllowProducts($product)
    {
        if (!$this->hasAllowProducts()) {
            $products = [];
            $skipSaleableCheck = $this->catalogProduct->getSkipSaleableCheck();
            $allProducts = $product->getTypeInstance()->getUsedProducts($product, null);
            foreach ($allProducts as $_product) {
                if ($_product->isSaleable() || $skipSaleableCheck) {
                    $products[] = $_product;
                }
            }
            $this->setAllowProducts($products);
        }
        return $this->getData('allow_products');
    }

    public function getConfigurableAttributes($product)
    {
        // Collect options applicable to the configurable product
        $productAttributeOptions = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
        $configurableAttributes = [];

        foreach ($productAttributeOptions as $productAttribute) {
            $attributeFull = $this->catalogConfig
                ->getAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $productAttribute['attribute_code']
                );
            foreach ($productAttribute['values'] as $attribute) {
                $configurableAttributes[$productAttribute['store_label']]['values'][] = $attribute['store_label'];
            }
            $configurableAttributes[$productAttribute['store_label']]['attribute_code'] = $attributeFull['attribute_code'];
            $configurableAttributes[$productAttribute['store_label']]['attribute_id'] = $attributeFull['attribute_id'];
            $configurableAttributes[$productAttribute['store_label']]['is_filterable'] = $attributeFull['is_filterable'];
            $configurableAttributes[$productAttribute['store_label']]['frontend_input'] = $attributeFull['frontend_input'];
        }

        return $configurableAttributes;
    }

    public function getConfigurableChildren($product, $child_attributes_to_select, $reload)
    {
        if ($this->configurableChildren && !$reload)
            return $this->configurableChildren;
        $usedProductCollection = $product->getTypeInstance()->getUsedProductCollection($product);
        $usedProductCollection->addAttributeToSelect($child_attributes_to_select);
        $usedProductCollection->addMinimalPrice()
            ->addFinalPrice();
        $this->configurableChildren = $usedProductCollection;
        return $usedProductCollection;
    }

    public function getConfigurableChildrenIds($product)
    {
        $configurableChildrenIds = [];
        foreach ($this->getConfigurableChildren($product, array('id', 'sku', 'type_id'), false) as $child) {
            $configurableChildrenIds[] = $child->getId();
            if ($product->isInStock()) {
                if (method_exists($child, 'isSaleable') && !$child->isSaleable()) {
                    // the simple product is probably disabled (because its in stock)
                    continue;
                }
            }
        }

        return $configurableChildrenIds;
    }

    public function getProductParentIds($product)
    {
        return $this->configurable->getParentIdsByChild($product->getId());
    }

    public function getCategoryMap()
    {
        if (!$this->_categories) {
            $categoryMap = [];
            $categories = $this->getCategoryCollection();

            foreach ($categories as $category) {
                $categoryMap[] = [
                    'id' => $category->getId(),
                    'path' => $category->getPath(),
                    'parent_id' => $category->getParentId(),
                    'name' => $category->getName()
                ];
            }
            $this->_categories = $categoryMap;
        }
        return $this->_categories;
    }

    public function getCategoryPathsByProduct($product)
    {
        $productCategoriesIds = $product->getCategoryIds();
        $rootCategoryId = $this->getRootCategoryId();
        $paths = [];
        $category_names = [];
        $all_categories = $this->getCategoryCollectionRange($productCategoriesIds);
        foreach ($all_categories as $category) {
            if (in_array($category->getId(), $productCategoriesIds)) {
                $path = explode('/', $category['path']);
                //we don't want the root category for the entire site
                array_shift($path);
                if ($rootCategoryId
                    && is_array($path)
                    && isset($path[0])
                    && $path[0] != $rootCategoryId
                ) {
                    continue;
                }
                //we want more specific categories first
                $paths[] =  implode(':', array_reverse($path));
                $category_names[] = $category['name'];
            }
        }
        return [array_filter($paths), $category_names];
    }

    public function createChild($childName, $childAttributes, $childValue, $childParent)
    {
        return $this->xmlGenerator->createChild(
            $childName,
            $childAttributes,
            $childValue,
            $childParent
        );
    }

    public function renderAttributeXml($attr, $product, $productElem)
    {
        try {
            $action = $attr->getAttributeCode();
            $attrValue = $product->getData($action);
            if ($attrValue == null) {
                if (intval($attr->getIsRequired())) {
                    $attrValue = $attr->getDefaultValue();
                }
                if ($attrValue == null) {
                    return;
                }
            }

            $is_filterable = $attr->getIsFilterable();
            $attribute_label = $attr->getFrontendLabel();

            if (!array_key_exists($action, $this->attributesValuesCache)) {
                $this->attributesValuesCache[$action] = [];
            }

            if (!is_array($attrValue)) {
                switch ($attr->getFrontendInput()) {
                    case 'select':
                        if (method_exists($product, 'getAttributeText')) {
                            /**
                             * We generate key for cached attributes array
                             * we make it as string to avoid null to be a key
                             */
                            $attrValue = $this->getAttrValue($product, $attrValue, $action);
                        }
                        break;
                    case 'textarea':
                    case 'price':
                    case 'text':
                        break;
                    case 'boolean':
                        if ($attrValue == '0' || !$attrValue) {
                            $attrValue = 'No';
                        } else {
                            $attrValue = 'Yes';
                        }
                        break;
                    case 'multiselect':
                        if (method_exists($product, 'getAttributeText')) {
                            /**
                             * We generate key for cached attributes array
                             * we make it as string to avoid null to be a key
                             */
                            $attrValueStr = $this->getAttrValue($product, $attrValue, $action);
                        } else {
                            $attrValueStr = $product->getResource()
                                ->getAttribute($action)->getFrontend()->getValue($product);
                        }
                        $attrValue = $attrValueStr;
                        break;
                }

            } else {
                $attrValue = json_encode($attrValue);
            }

            if ($attrValue) {
                $attrs = [
                    'is_filterable' => $is_filterable,
                    'name' => $attr->getAttributeCode()
                ];

                if (in_array($attr->getAttributeCode(), [
                    'special_from_date',
                    'special_to_date',
                    'news_to_date',
                    'news_from_date'
                ])) {
                    $localDate = new \DateTime(
                        $attrValue, new \DateTimeZone(
                            $this->helper->getTimezone($this->getStoreId())
                        )
                    );
                    $attrValue = $localDate->format('Y-m-d H:i:d');
                    $attrs['dt'] = $localDate->getTimestamp();
                }

                $attributeElem = $this->createChild(
                    'attribute', $attrs, false, $productElem
                );

                $this->createChild(
                    'attribute_values',
                    false,
                    $attrValue,
                    $attributeElem
                );
                $this->createChild(
                    'attribute_label',
                    false,
                    $attribute_label,
                    $attributeElem
                );
            }
        } catch (\Exception $e) {
            $this->logger->warn(print_r($e->getTraceAsString(), true));
            $this->logger->warn($e->getMessage());
        }
    }

    public function renderProductVariantXml($product, $productElem)
    {
        if ($this->helper->canUseProductAttributes()) {
            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                $variants = [];
                $variant_codes = [];
                $configurableAttributes = $this->getConfigurableAttributes($product);
                foreach ($configurableAttributes as $attrName => $confAttrN) {
                    if (is_array($confAttrN) && array_key_exists('values', $confAttrN)) {
                        $variants[] = $attrName;
                        $variant_codes[] = $confAttrN['attribute_code'];
                        $values = implode(' , ', $confAttrN['values']);
                        $this->createChild(
                            'attribute', [
                            'is_configurable' => 1,
                            'is_filterable' => $confAttrN['is_filterable'],
                            'name' => $attrName
                        ], $values, $productElem
                        );
                    }
                }

                if (count($variants) > 0) {
                    $simpleSkusArr = [];
                    $variantElem = $this->createChild('variants', false, false, $productElem);

                    $child_attributes_to_select = array(
                        'special_price',
                        'image',
                        'small_image',
                        'product_thumbnail_image',
                        'visibility',
                        'type_id',
                        'name',
                        'status',
                        'qty',
                        'sku'
                    );
                    $child_attributes_to_select = array_merge($child_attributes_to_select, $variant_codes);
                    $configChildren = $this->getConfigurableChildren($product, $child_attributes_to_select, true);
                    foreach ($configChildren as $child_product) {

                        /**
                         *  if (!in_array($product->getStoreId(), $child_product->getStoreIds())) {
                         *   //continue;
                         *  }
                         */
                        if ($this->getRulesCount() > 0) {
                            if ($child_product->getCatalogRulePrice()) {
                                $this->catalogRuleAffectedProducts[(int)$child_product->getId()] = $child_product;
                            } else {
                                $this->catalogFutureRuleAffectedProducts[] = (int)$child_product->getId();
                            }
                        }

                        $stockitem = $this->stockRegistry
                            ->getStockItem(
                                $child_product->getId(),
                                $product->getStore()->getWebsiteId()
                            );
                        $varStockSource = $this->helper->useQtyAsStockSource();
                        if ($varStockSource != 'qty') {
                            $is_variant_in_stock = ($stockitem->getIsInStock()) ? 1 : 0;
                        } else {
                            $is_variant_in_stock = ($stockitem->getQty() > 0) ? 1 : 0;
                        }

                        $imagePath = $child_product->getImage() ? $child_product->getImage() : $child_product->getSmallImage();
                        $_baseImage = $this->storeManager
                                ->getStore()
                                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                            . 'catalog/product' . $imagePath;

                        if (strpos($_baseImage, 'no_selection') !== false) {
                            $_baseImage = $this->image->init($child_product, 'product_thumbnail_image')->getUrl();
                        }

                        $is_variant_sellable = '';
                        if (method_exists($child_product, 'isSaleable')) {
                            $is_variant_sellable = ($child_product->isSaleable()) ? 1 : 0;
                            if (!$is_variant_sellable && $child_product->hasData('is_salable')) {
                                $is_variant_sellable = ($child_product->getData('is_salable')) ? 1 : 0;
                            }
                        }

                        if (method_exists($child_product, 'getVisibility')) {
                            $is_variant_visible = ($child_product->getVisibility()) ? 1 : 0;
                        } else {
                            $is_variant_visible = '';
                        }
                        $variantFinalPrice = $this->priceCurrencyInterface->convertAndRound($child_product->getFinalPrice());
                        $variantCatalogRulePrice = $this->getCatalogRulePrice($child_product);
                        if ($variantCatalogRulePrice) {
                            $variantFinalPrice = min($variantCatalogRulePrice, $variantFinalPrice);
                        }
                        $variant_node_attributes = [
                            'id' => $child_product->getId(),
                            'type' => $child_product->getTypeId(),
                            'visibility' => $is_variant_visible,
                            'is_in_stock' => $is_variant_in_stock,
                            'is_seallable' => $is_variant_sellable,
                            'price' => $variantFinalPrice,
                            'sku' => $child_product->getSku()
                        ];
                        $matches = [];
                        preg_match('/.*\.(jpg|jpeg|png|gif)$/', $_baseImage, $matches);
                        if (count($matches) > 0) {
                            $variant_node_attributes['variantimage'] = $_baseImage;
                        }

                        $productVariation = $this->createChild(
                            'variant',
                            $variant_node_attributes,
                            false,
                            $variantElem
                        );

                        $this->createChild(
                            'name',
                            false,
                            $child_product->getName(),
                            $productVariation
                        );

                        foreach ($variant_codes as $attribute_code) {
                            try {
                                $attribute = $this->productAttributeRepository->get(strtolower($attribute_code));
                            } catch (\Exception $e) {
                                //echo $e->getMessage();
                                continue;
                            }
                            $variant_name = !$attribute->getData('store_label')? $attribute->getData('frontend_label') : $attribute->getData('store_label');
                            $attrValue = '';
                            try {
                                $attrValue = $this->getAttrValue(
                                    $child_product,
                                    $child_product->getData($attribute->getAttributeCode()),
                                    $attribute->getAttributeCode()
                                );
                            } catch (\Exception $e) {
                                //echo $e->getMessage();
                            }

                            $this->createChild(
                                'variant_attribute',
                                [
                                    'is_configurable' => 1,
                                    'is_filterable' => $attribute->getIsFilterable(),
                                    'name' => $variant_name,
                                    'name_code' => $attribute->getId(),
                                    'value_code' => $child_product->getData($attribute->getAttributeCode())
                                ],
                                $attrValue,
                                $productVariation
                            );
                        }

                        if ($is_variant_sellable == 1) {
                            $simpleSkusArr[] = $child_product->getSku();
                        }
                    }

                    $attributeElem = $this->createChild(
                        'attribute', [
                        'is_filterable' => 0,
                        'name' => 'configurable_simple_skus'
                    ], false, $productElem
                    );
                    $this->createChild(
                        'attribute_values',
                        false,
                        implode(',', $simpleSkusArr),
                        $attributeElem
                    );
                    $this->createChild(
                        'attribute_label',
                        false,
                        'configurable_simple_skus',
                        $attributeElem
                    );
                }
            }
        }
    }

    public function renderCatalogXml(
        $offset,
        $count,
        $storeId,
        $orders,
        $interval
    ) {
        $this->setOffset($offset);
        $this->setCount($count);
        $this->setStoreId($storeId);
        $this->setOrders($orders);
        $this->setInterval($interval);

        $productCollection = $this->getProductCollection();

        $productCollection->getSelect()->limit($count, $offset);

        $productCollection->addMinimalPrice()
            ->addFinalPrice();

        $productCollection->addAttributeToSelect('price');
        $this->changePriceIndexJoinType($productCollection);

        $productCollection->addTierPriceData();

        $this->appendReviews();

        $this->setRulesCount($this->getActiveRulesCount());

        foreach ($productCollection as $product) {
            $this->renderProduct($product, 'insert');
        }

        $dateTs = $this->_localeDate->scopeTimeStamp($storeId);
        if (count($this->catalogRuleAffectedProducts) > 0) {
            $resultsToSchedule = $this->getActiveRulesFromProducts(
                $this->storeManager->getStore()->getWebsiteId(),
                $this->catalogRuleAffectedProducts,
                $dateTs
            );
            foreach ($resultsToSchedule as $res) {
                $this->scheduleDistantUpdate(
                    null,
                    date('Y-m-d', $res['to_time']),
                    $dateTs,
                    $this->catalogRuleAffectedProducts[$res['product_id']]
                );
            }
        }

        if (count($this->catalogFutureRuleAffectedProducts) > 0) {
            $resultsToSchedule = $this->getFutureRulesFromProducts(
                $this->storeManager->getStore()->getWebsiteId(),
                $this->catalogFutureRuleAffectedProducts,
                $dateTs
            );
            foreach ($resultsToSchedule as $res) {
                $this->scheduleDistantUpdate(
                    date('Y-m-d', $res['from_time']),
                    null,
                    $dateTs,
                    $res['product_id']
                );
            }
        }

        $this->flushUpdatesBuffer();
        return $this->xmlGenerator->generateXml();
    }

    /**
     * @return mixed
     */
    public function getRulesCount()
    {
        return $this->rulesCount;
    }

    /**
     * @param mixed $rulesCount
     */
    public function setRulesCount($rulesCount)
    {
        $this->rulesCount = $rulesCount;
    }

    /**
     * Get active rule data based on few filters
     *
     * @param  int|string $date
     * @param  int        $websiteId
     * @param  int        $customerGroupId
     * @param  int        $productId
     * @return array
     */
    public function getActiveRulesFromProducts($websiteId, $products, $dateTs)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('catalogrule_product'), ['to_time', 'product_id'])
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', 0)
            ->where('product_id IN (?)', array_keys($products))
            ->where('to_time BETWEEN ? AND NOW() + INTERVAL 1 MONTH', $dateTs);

        return $connection->fetchAll($select);
    }

    /**
     * Get active rule data based on few filters
     *
     * @param  int|string $date
     * @param  int        $websiteId
     * @param  int        $customerGroupId
     * @param  int        $productId
     * @return array
     */
    public function getFutureRulesFromProducts($websiteId, $productsIds, $dateTs)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('catalogrule_product'), ['from_time', 'product_id'])
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', 0)
            ->where('product_id IN (?)', $productsIds)
            ->where('from_time > ?', $dateTs);

        return $connection->fetchAll($select);
    }

    public function makeRemoveRow($batch)
    {
        $productElement = $this->createChild(
            'product', [
            'updatedate' =>  ($batch->getUpdateDate()),
            'action'    =>  $batch->getAction(),
            'id'    =>  $batch->getProductId(),
            'storeid'   =>  $batch->getStoreId()
        ], false, $this->xmlGenerator->getSimpleXml()
        );

        $this->createChild('sku', false, $batch->getSku(), $productElement);
        $this->createChild('id', false, $batch->getProductId(), $productElement);
    }

    public function getSingleBatchTableRecord($id, $store_id)
    {
        /**
         * Load and filter the batches
         */
        $batchCollection = $this->getBatchCollection();
        $batchCollection
            ->addFieldToFilter('product_id', $id)
            ->addFieldToFilter('store_id', $store_id);
        $batchCollection->setOrder('update_date');

        $max_update_date = 0;
        $batches = [];

        foreach ($batchCollection as $batch) {
            if (intval($batch['update_date']) > $max_update_date) {
                $max_update_date = $batch['update_date'];
            }
            $batches[] = [
                'product_id' => $batch['product_id'],
                'action' => $batch['action'],
                'update_date' => $batch['update_date'],
                'store_id' => $batch['store_id']
            ];
        }

        return json_encode(
            [
                'max_update_date' => $max_update_date,
                'batches' => $batches
            ]
        );
    }

    public function renderUpdatesCatalogXml(
        $count,
        $storeId,
        $from,
        $to,
        $page,
        $send_oos
    ) {
        /**
         * Load and filter the batches
         */
        $batchCollection = $this->getBatchCollection();
        $filter = ['from' => $from];
        if ($to > 0) {
            $filter['to'] = $to;
        }
        $batchCollection
            ->addFieldToFilter('update_date', $filter)
            ->addFieldToFilter('store_id', $storeId);
        $batchCollection->setOrder('update_date', 'ASC');
        $offset = $page == 1 ? 0 : ($page - 1) * $count;
        $batchCollection->getSelect()->limit($count, $offset);

        /**
         * Set required data for retrieving OrdersPerProduct
         */
        $this->setStoreId($storeId);
        $this->setInterval(12);

        /**
         * We need to reset the root attributes on <catalog />
         */
        $this->xmlGenerator->setRootAttributes(
            [
                'version'   =>  $this->helper->getVersion(),
                'magento'   =>  $this->helper->getMagentoVersion(),
                'fromdatetime'  =>  $from
            ]
        );

        $updatesBulk = [];
        $productIds = [];
        foreach ($batchCollection as $batch) {
            $productId = $batch->getProductId();
            $batchStoreId = $batch->getStoreId();

            if ($storeId !== $batchStoreId) {
                $currency = $this->storeManager->getStore($batchStoreId)->getCurrentCurrencyCode();
            }

            $product = null;

            if ($batch->getAction() == 'update') {
                if ($productId) {
                    $updatesBulk[$productId] = $batch;
                    $productIds[] = $productId;
                } else {
                    $batch->setAction('remove');
                    $this->makeRemoveRow($batch);
                    continue;
                }
            } else {
                $batch->setAction('remove');
                $this->makeRemoveRow($batch);
                continue;
            }
        }

        $productCollection = $this->getProductCollection(false);

        if (is_numeric($storeId)) {
            $productCollection->addStoreFilter($storeId);
            $productCollection->setStoreId($storeId);
        }

        $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds]);

        $productCollection->addMinimalPrice()
            ->addFinalPrice();

        $productCollection->addAttributeToSelect('price');
        $this->changePriceIndexJoinType($productCollection);
        $productCollection ->addTierPriceData();

        $this->appendReviews();

        $this->setRulesCount($this->getActiveRulesCount());

        $visibleProductIds = [];
        foreach ($productCollection as $product) {
            $batch = $updatesBulk[$product->getId()];
            $this->renderProduct(
                $product,
                'update',
                $batch->getUpdateDate(),
                $batch->getStoreId()
            );
            $visibleProductIds[] = $product->getId();
        }

        $notVisisbleProducts = array_diff($productIds, $visibleProductIds);

        foreach ($notVisisbleProducts as $productId) {
            $batch = $updatesBulk[$productId];
            $stockItem = $this->stockFactory->load($productId, 'product_id');

            if ($stockItem->getTypeId() == 'configurable') {
                $product = $this->productModel->load($productId);
                if (!$send_oos && $product->hasData('is_salable') && !boolval($product->getData('is_salable'))) {
                    $batch->setAction('remove');
                } else {
                    $batch->setAction('ignore');
                }
            } else {
                if (!$send_oos && (!$stockItem || count($stockItem->getData()) == 0 || !boolval($stockItem->getIsInStock()))) {
                    $batch->setAction('remove');
                } else {
                    $batch->setAction('ignore');
                }
            }

            $this->makeRemoveRow($batch);
        }

        $dateTs = $this->_localeDate->scopeTimeStamp($storeId);
        if (count($this->catalogRuleAffectedProducts) > 0) {
            $resultsToSchedule = $this->getActiveRulesFromProducts(
                $this->storeManager->getStore()->getWebsiteId(),
                $this->catalogRuleAffectedProducts,
                $storeId,
                $dateTs
            );
            foreach ($resultsToSchedule as $res) {
                $this->scheduleDistantUpdate(null, date('Y-m-d', $res['to_time']), $dateTs, $this->catalogRuleAffectedProducts[$res['product_id']]);
            }
        }

        if (count($this->catalogFutureRuleAffectedProducts) > 0) {
            $resultsToSchedule = $this->getFutureRulesFromProducts(
                $this->storeManager->getStore()->getWebsiteId(),
                $this->catalogFutureRuleAffectedProducts,
                $storeId,
                $dateTs
            );
            foreach ($resultsToSchedule as $res) {
                $this->scheduleDistantUpdate(date('Y-m-d', $res['from_time']), null, $dateTs, $res['product_id']);
            }
        }
        $this->flushUpdatesBuffer();
        return $this->xmlGenerator->generateXml();
    }

    public function renderCatalogByIds($ids, $storeId = 0)
    {
        /**
         * We need to reset the root attributes on <catalog />
         */
        $this->xmlGenerator->setRootAttributes(
            [
                'version'   =>  $this->helper->getVersion(),
                'magento'   =>  $this->helper->getMagentoVersion()
            ]
        );

        $this->loopOverProductCollectionByIds($ids, $storeId, 'getbyid');

        return $this->xmlGenerator->generateXml();
    }

    /**
     * renderTieredPrices
     *
     * @param $product
     * @param $productXmlElem
     *
     * @return void
     */
    protected function renderTieredPrices($product, $productXmlElem)
    {
        if ($product->getTypeId() != Grouped::TYPE_CODE) {
            if (is_array($product->getData('tier_price'))
                && count($product->getData('tier_price')) > 0
            ) {
                $tieredPricesElem = $this->createChild(
                    'tiered_prices',
                    false,
                    false,
                    $productXmlElem
                );

                foreach ($product->getData('tier_price') as $trP) {
                    $this->createChild(
                        'tiered_price',
                        [
                            'cust_group' => array_key_exists($trP['cust_group'], $this->_customersGroups) ?
                                $this->_customersGroups[$trP['cust_group']] : $trP['cust_group'],
                            'cust_group_id' => $trP['cust_group'],
                            'price' => $trP['price'],
                            'min_qty' => $trP['price_qty']
                        ],
                        false,
                        $tieredPricesElem
                    );
                }
            }
        } else {
            $products = $this->grouped->getAssociatedProducts($product);
            $minPrice = 2147483647;
            $cheapestProduct = null;
            foreach ($products as $grProduct) {
                if ($grProduct->getFinalPrice() < $minPrice) {
                    $minPrice = $grProduct->getFinalPrice();
                    $cheapestProduct = $grProduct;
                }
            }
            if ($cheapestProduct != null) {
                $product->setData('tier_price', $cheapestProduct->getTierPrices());
                if (is_array($product->getData('tier_price'))
                    && count($product->getData('tier_price')) > 0
                ) {
                    $tieredPricesElem = $this->createChild(
                        'tiered_prices',
                        false,
                        false,
                        $productXmlElem
                    );
                    foreach ($product->getData('tier_price') as $trP) {
                        $max_price = 0;
                        foreach ($products as $assoc_prod) {
                            $tiered_prices = $assoc_prod->getTierPrices();
                            $tiered_price_exists = false;
                            foreach ($tiered_prices as $assoc_prod_t_pr) {
                                if ($trP->getCustomerGroupId() == $assoc_prod_t_pr->getCustomerGroupId()) {
                                    if ($assoc_prod_t_pr->getQty() == 1) {
                                        if ($max_price < $assoc_prod_t_pr->getValue()) {
                                            $max_price = $assoc_prod_t_pr->getValue();
                                        }
                                        $tiered_price_exists = true;
                                        break;
                                    }
                                }
                            }
                            if (!$tiered_price_exists) {
                                if ($max_price < $assoc_prod->getFinalPrice()) {
                                    $max_price = $assoc_prod->getFinalPrice();
                                }
                            }
                        }
                        $this->createChild(
                            'tiered_price',
                            [
                                'cust_group' => array_key_exists($trP['cust_group'], $this->_customersGroups) ?
                                    $this->_customersGroups[$trP->getCustomerGroupId()] : $trP->getCustomerGroupId(),
                                'cust_group_id' => $trP->getCustomerGroupId(),
                                'price' => $trP->getValue(),
                                'min_qty' => $trP->getQty(),
                                'max_price' => $max_price
                            ],
                            false,
                            $tieredPricesElem
                        );
                    }
                }
            }
        }
    }

    /**
     * GeneratePriceRange
     *
     * @return array
     */
    public function generatePriceRange($product, $finalPrice)
    {
        $min_price = 0;
        $max_price = 0;
        $compare_at_price = 0;
        $pricesToCompare = array();
        if ($finalPrice && $finalPrice > 0) {
            $pricesToCompare[] = $finalPrice;
        }

        if ($product->getMinimalPrice() && $product->getMinimalPrice() > 0) {
            $pricesToCompare[] = $this->priceCurrencyInterface->convertAndRound((float)$product->getMinimalPrice());
        }

        if ($product->getMaxPrice() && $product->getMaxPrice() > 0) {
            $max_price = $this->priceCurrencyInterface->convertAndRound((float)$product->getMaxPrice());
        }

        foreach ($this->getConfigurableChildren($product, array('id', 'sku', 'type_id', 'special_price'), false) as $child) {
            if ($child->getPrice() && $compare_at_price < $child->getPrice() && $child->getFinalPrice() < $child->getPrice()) {
                $compare_at_price = $this->priceCurrencyInterface->convertAndRound((float)$child->getPrice());
            }

            if ($child->getFinalPrice() && $child->getFinalPrice() > 0) {
                $pricesToCompare[] = $this->priceCurrencyInterface->convertAndRound((float)$child->getFinalPrice());
            }
            if ($child->getCatalogRulePrice() && $child->getCatalogRulePrice() > 0) {
                $pricesToCompare[] = $this->priceCurrencyInterface->convertAndRound((float)$child->getCatalogRulePrice());
            }
        }

        if (count($pricesToCompare) > 0) {
            $min_price = min($pricesToCompare);
            $pricesToCompare[] = $max_price;
            $max_price = max($pricesToCompare);
        }

        $regularPrice = $this->priceCurrencyInterface->convertAndRound((float)$product->getRegularPrice());
        if ($regularPrice > $compare_at_price) {
            $compare_at_price = $regularPrice;
        }

        if ($compare_at_price <= $min_price)
            $compare_at_price = 0;

        $price_range = [
            'price_min' => $min_price,
            'price_max' => $max_price
        ];

        if ($compare_at_price > 0) {
            $price_range['compare_at_price'] = $compare_at_price;
        }

        return $price_range;
    }

    protected function _getPurchasePopularity($product)
    {
        if ($this->getMinimumOrder() < 0) {
            return 0;
        }
        $connection = $this->resourceConnection->getConnection();
        $sales_order_item_table_name = $this->resourceConnection->getTableName('sales_order_item');
        $sql = $connection->select()
            ->from($sales_order_item_table_name, 'SUM(qty_ordered) AS qty_ordered')
            ->where('store_id = ?', $this->getStoreId())
            ->where('product_id = ?', $product->getId())
            ->where('order_id >= ?', $this->getMinimumOrder());
        $results = $connection->fetchAll($sql);
        foreach ($results as $order_item) {
            if (is_array($order_item) && array_key_exists('qty_ordered', $order_item)) {
                return $order_item['qty_ordered'];
            }
        }
        return 0;
    }

    protected function _getProductEnabledString($product)
    {
        return intval(($product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) ? '1' : '0');
    }

    /**
     * getProductFinalPrice this method allows to fetch product price
     * according to product type specificality
     *
     * @param $product
     *
     * @return mixed
     */
    protected function getProductFinalPrice($product)
    {
        $finalPrice = $product->getPriceInfo()->getPrice('final_price')
            ->getValue();
        return $finalPrice;
    }

    protected function getCatalogRulePrice($product) {
        $price = $this->ruleModel
            ->getRulePrice(
                $this->_localeDate->scopeDate($this->storeManager->getStore()->getId()),
                $this->storeManager->getStore()->getWebsiteId(),
                0,
                $product->getId()
            );
        return $this->priceCurrencyInterface->convertAndRound($price);
    }

    /**
     * @param $product
     */
    protected function renderProduct(
        $product,
        $action = 'update',
        $updatedate = 0,
        $storeId = null
    ) {
        try {
            $product->getTypeInstance()->setStoreFilter($this->storeManager->getStore(), $product);
            $this->configurableChildren = null;

            if ($this->image->init($product, 'instant_search_product_thumbnail_image')->getWidth()) {
                $_thumbs = $this->image->init($product, 'instant_search_product_thumbnail_image')->getUrl();
            } else {
                $_thumbs = $this->image->init($product, 'product_thumbnail_image')->getUrl();
            }

            $imagePath = $product->getSmallImage() ? $product->getSmallImage() : $product->getImage();
            if ($imagePath && substr( $imagePath, 0, 1 ) !== "/") {
                $imagePath = '/' . $imagePath;
            }
            $_baseImage = $this->storeManager
                    ->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product' . $imagePath;

            $productPrices = array();
            $finalPrice = $this->priceCurrencyInterface->convertAndRound($product->getFinalPrice());

            $priceRange = ['price_min' => 0, 'price_max' => 0];

            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                $priceRange = $this->generatePriceRange($product, $finalPrice);
            }

            $scheduled = false;
            $specialFromDate = $product->getSpecialFromDate();
            $specialToDate = $product->getSpecialToDate();
            $specialPrice = $this->priceCurrencyInterface->convertAndRound($product->getSpecialPrice());
            $nowDateGmt = strtotime('now');
            if (!is_null($specialPrice) && $specialPrice != false) {
                $scheduled = $this->scheduleDistantUpdate($specialFromDate, $specialToDate, $nowDateGmt, $product);
            }

            if ($this->productMetadata->getEdition() != 'Community') {
                $nextScheduledStagingTime = $this->getNextProductScheduledUpdateDateById($product->getId());
                if($nextScheduledStagingTime) {
                    $scheduled = $this->scheduleDistantUpdate($nextScheduledStagingTime, null, $nowDateGmt, $product);
                }
            }

            if ($updatedate && $updatedate > $nowDateGmt) {
                $lastModifiedDate = strtotime(
                    (string) $product->getUpdatedAt()
                );
            } else {
                $lastModifiedDate = $updatedate;
            }

            if ($product->getTypeId() == Grouped::TYPE_CODE
                || $product->getTypeId() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                $priceRange = [
                    'price_min' => $this->priceCurrencyInterface->convertAndRound($product->getMinPrice()),
                    'price_max' => $this->priceCurrencyInterface->convertAndRound($product->getMaxPrice())
                ];
                if ($finalPrice == 0) {
                    $finalPrice = $priceRange['price_min'];
                }
            }

            if ($this->getRulesCount() > 0) {
                if ($product->getCatalogRulePrice() && $product->getCatalogRulePrice() > 0) {
                    $this->catalogRuleAffectedProducts[(int)$product->getId()] = $product;
                } else {
                    $this->catalogFutureRuleAffectedProducts[] = (int)$product->getId();
                }
            }

            $productPrices[] = $finalPrice;
            $catalogRulePrice = $this->getCatalogRulePrice($product);
            if ($catalogRulePrice && $catalogRulePrice > 0) {
                $productPrices[] = $catalogRulePrice;
            }
            if ($priceRange['price_min'] && $priceRange['price_min'] > 0) {
                $productPrices[] = $priceRange['price_min'];
            }
            $finalPrice = min($productPrices);

            $currency = $this->getCurrencyCode();
            $xmlAttributes = [
                'action' => $action,
                'id' => $product->getId(),
                'thumbs' => $_thumbs,
                'base_image' => $_baseImage,
                'url' => $product->getProductUrl(true),
                'price' => $finalPrice,
                'price_min' => ($priceRange['price_min']),
                'price_max' => ($priceRange['price_max']),
                'type' => $product->getTypeId(),
                'currency' => $currency,
                'visibility' => $product->getVisibility(),
                'selleable' => $product->isSalable()
            ];

            if ($lastModifiedDate != 0) {
                $xmlAttributes['updatedate'] = $lastModifiedDate;
            }
            if ($storeId != null) {
                $xmlAttributes['storeid'] = $storeId;
            }

            $productPrice = $this->priceCurrencyInterface->convertAndRound($product->getPrice());
            if ($productPrice && $productPrice > 0) {
                $productPrices[] = $productPrice;
            }

            $regularPrice = $this->priceCurrencyInterface->convertAndRound($product->getRegularPrice());
            if ($regularPrice && $regularPrice > 0) {
                $productPrices[] = $regularPrice;
            }

            $raw_msrp = $product->getMsrp();
            if (!$raw_msrp) {
                $raw_msrp = $product->getgcm_msrp();
            }
            $msrp = round(floatval($raw_msrp), 2);
            $msrp = $this->priceCurrencyInterface->convertAndRound($msrp);
            if ($msrp && $msrp > 0) {
                $productPrices[] = $msrp;
            }

            if ($product->getTypeId() == Configurable::TYPE_CODE && array_key_exists('compare_at_price', $priceRange)) {
                $xmlAttributes['price_compare_at_price'] = $priceRange['compare_at_price'];
            } else {
                $compare_at_price = max($productPrices);
                if ($compare_at_price > $finalPrice) {
                    $xmlAttributes['price_compare_at_price'] = $compare_at_price;
                }
            }

            $productElem = $this->createChild('product', $xmlAttributes, false, $this->xmlGenerator->getSimpleXml());

            $this->createChild(
                'description',
                false,
                strval($product->getDescription()),
                $productElem
            );

            $this->createChild(
                'short',
                false,
                strval($product->getShortDescription()),
                $productElem
            );

            $this->createChild(
                'name',
                false,
                strval($product->getName()),
                $productElem
            );

            $this->createChild(
                'sku',
                false,
                strval($product->getSku()),
                $productElem
            );

            $ratingSummary = $product->getRatingSummary();

            if ($ratingSummary) {
                $this->createChild(
                    'review',
                    false,
                    intval($ratingSummary->getRatingSummary()),
                    $productElem
                );

                $this->createChild(
                    'review_count',
                    false,
                    intval($ratingSummary->getReviewsCount()),
                    $productElem
                );
            }

            if (filter_var($this->getOrders(), FILTER_VALIDATE_BOOLEAN)) {
                $purchasePopularity = $this->_getPurchasePopularity($product);
                $this->createChild('purchase_popularity', false, intval($purchasePopularity), $productElem);
            }

            $_isEnabled = $this->_getProductEnabledString($product);
            $this->createChild('product_status', false, $_isEnabled, $productElem);

            $creation_date = null;

            if ($product->getNewsFromDate()) {
                $newFromDtGmt = $this->dateTime->timestamp($product->getNewsFromDate());
                $nowDateGmt = strtotime('now');
                if ($newFromDtGmt < $nowDateGmt) {
                    $creation_date = $newFromDtGmt;
                }
                $newToDtGmt = null;
                if ($product->getNewsToDate()) {
                    $newToDtGmt = $this->dateTime->timestamp($product->getNewsToDate());
                    if ($newToDtGmt < $nowDateGmt) {
                        $creation_date = null;
                    }
                    $this->createChild(
                        'newto',
                        false,
                        $newToDtGmt,
                        $productElem
                    );
                }

                $this->createChild(
                    'newfrom',
                    false,
                    $newFromDtGmt,
                    $productElem
                );

                $scheduledTemp = $this->scheduleDistantUpdate($product->getNewsFromDate(), $product->getNewsToDate(), $nowDateGmt, $product);
                if (!$scheduled) {
                    $scheduled = $scheduledTemp;
                }
            }

            if ($scheduled) {
                $this->createChild(
                    'update_scheduled',
                    ['scheduled_date' => $this->scheduledUpdatesBuffer[$product->getId()]['update_date']],
                    $scheduled,
                    $productElem
                );
            }


            if (!$creation_date) {
                $creation_date = $this->dateTime->timestamp($product->getCreatedAt());
            }
            $this->createChild(
                'creation_date',
                false,
                $creation_date,
                $productElem
            );
            $this->createChild(
                'updated_date',
                false,
                $this->dateTime->timestamp($product->getUpdatedAt()),
                $productElem
            );

            if ($this->helper->canUseProductAttributes()) {
                $attributeSetId = $product->getAttributeSetId();
                $this->getAttributesSetCachedById($attributeSetId);
                if (!array_key_exists($attributeSetId, $this->attributesSetsCache)) {
                    $this->attributesSetsCache[$attributeSetId] = [];
                    $setAttributes = $this->productAttributeManagementInterface->getAttributes(
                        \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
                        $attributeSetId
                    );

                    foreach ($setAttributes as $attrFromSet) {
                        $this->attributesSetsCache[$attributeSetId][] = $attrFromSet->getAttributeCode();
                    }
                }

                foreach ($this->getProductAttributes() as $attr) {
                    $this->renderAttributeXml($attr, $product, $productElem);
                }
            }

            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                $this->createChild(
                    'simpleproducts',
                    false,
                    implode(',', $this->getConfigurableChildrenIds($product)),
                    $productElem
                );

                $this->renderProductVariantXml($product, $productElem);
            }

            $cats_data = $this->getCategoryPathsByProduct($product);
            $this->createChild(
                'categories',
                false,
                implode(';', $cats_data[0]),
                $productElem
            );

            $attributeElem = $this->createChild(
                'attribute', [
                'is_filterable' => 0,
                'name' => 'category_names'
            ], false, $productElem
            );
            $this->createChild(
                'attribute_values',
                false,
                implode(',', $cats_data[1]),
                $attributeElem
            );
            $this->createChild(
                'attribute_label',
                false,
                'category_names',
                $attributeElem
            );

            $this->createChild(
                'meta_title',
                false,
                strval($product->getMetaTitle()),
                $productElem
            );
            $this->createChild(
                'meta_description',
                false,
                strval($product->getMetaDescription()),
                $productElem
            );
            $this->createChild(
                'meta_keywords',
                false,
                strval($product->getMetaKeyword()),
                $productElem
            );

            $this->renderTieredPrices($product, $productElem);

            if ($product->getTypeId() == Grouped::TYPE_CODE) {
                $this->renderGroupedChildrenSkus($product, $productElem);
            }
        } catch (\Exception $e) {
            echo $e->getTraceAsString();
            echo '<br/>';
            echo $e->getMessage();
            $this->logger->warn($e->getMessage());
        }
    }

    /**
     * @param $product
     * @param $productElem
     */
    protected function renderGroupedChildrenSkus($product, $productElem)
    {
        $childProductCollection = $product->getTypeInstance()
            ->getAssociatedProducts($product);
        $childSkus = [];
        foreach ($childProductCollection as $childProduct) {
            if ($childProduct->getTypeId() == Configurable::TYPE_CODE) {
                $configChildren = $this->getConfigurableChildren($childProduct, array('sku', 'type_id'), false);
                foreach ($configChildren as $configChild) {
                    $childSkus[] = $configChild->getSku();
                }
            } else {
                $childSkus[] = $childProduct->getSku();
            }
        }
        if (count($childSkus) > 0) {
            $attributeElem = $this->createChild(
                'attribute', [
                'is_filterable' => 0,
                'name' => 'configurable_simple_skus'
            ], false, $productElem
            );
            $this->createChild(
                'attribute_values',
                false,
                implode(',', $childSkus),
                $attributeElem
            );
            $this->createChild(
                'attribute_label',
                false,
                'configurable_simple_skus',
                $attributeElem
            );
        }
    }

    /**
     * @param $specialFromDate
     * @param $specialToDate
     */
    protected function scheduleDistantUpdate($specialFromDate, $specialToDate, $nowDateGmt, $product)
    {
        $scheduled = false;
        if (is_numeric($product)) {
            $product = $this->productModel->load($product);
        }
        $specialFromDateGmt = null;
        if ($specialFromDate != null) {
            if (!is_integer($specialFromDate)) {
                $localDate = new \DateTime(
                    $specialFromDate, new \DateTimeZone(
                        $this->helper->getTimezone($this->getStoreId())
                    )
                );
                $specialFromDateGmt = $localDate->getTimestamp();
            } else {
                $specialFromDateGmt = $specialFromDate;
            }
        }
        if ($specialFromDateGmt && $specialFromDateGmt > $nowDateGmt) {
            $scheduled = $this->updateScheduledUpsertsBuffer($product, $specialFromDateGmt);
        } elseif ($specialToDate != null) {
            if (!is_integer($specialToDate)) {
                $localDate = new \DateTime(
                    $specialToDate, new \DateTimeZone(
                        $this->helper->getTimezone($this->getStoreId())
                    )
                );
                $hour = $localDate->format('H');
                $mins = $localDate->format('i');
                if ($hour == '00' && $mins == '00') {
                    $localDate->modify('+86700 seconds'); //make "to" limit inclusive and another 5 minutes for safety
                }
                $specialToDateGmt = $localDate->getTimestamp();
            } else {
                $specialToDateGmt = $specialToDate;
            }
            if ($specialToDateGmt > $nowDateGmt) {
                $scheduled = $this->updateScheduledUpsertsBuffer($product, $specialToDateGmt);
            }
        }
        return $scheduled;
    }

    /**
     * @param  $ids
     * @param  $storeId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function loopOverProductCollectionByIds($ids, $storeId, $action)
    {
        $productCollection = $this->getProductCollection(false);
        $this->setStoreId($storeId);
        if (is_numeric($storeId)) {
            $productCollection->addStoreFilter($storeId);
            $productCollection->setStoreId($storeId);
        }

        $productCollection->addAttributeToFilter('entity_id', ['in' => $ids]);

        $productCollection->addMinimalPrice()
            ->addFinalPrice();

        $productCollection->addAttributeToSelect('price');

        $this->changePriceIndexJoinType($productCollection);

        $productCollection->addTierPriceData();

        $this->appendReviews();

        $visibleProductIds = [];
        foreach ($productCollection as $product) {
            $this->renderProduct($product, $action);
            $visibleProductIds[] = $product->getId();
        }

        $notVisisbleProducts = array_diff($ids, $visibleProductIds);

        if (count($notVisisbleProducts) > 0) {
            foreach ($notVisisbleProducts as $productId) {

                $stockItem = $this->stockFactory->load($productId, 'product_id');
                $itemType = $stockItem->getTypeId();
                $action = '';
                $sku = '';
                if ($itemType == 'configurable') {
                    $product = $this->productModel->load($productId);
                    $sku = $product->getSku();
                    if ($product->hasData('is_salable') && !boolval($product->getData('is_salable'))) {
                        $action = 'remove';
                    } else {
                        $action = 'ignore';
                    }
                } else {
                    if ((!$stockItem || count($stockItem->getData()) == 0 ||  !boolval($stockItem->getIsInStock()))) {
                        $action = 'remove';
                    } else {
                        $action = 'ignore';
                    }
                }

                $productElement = $this->createChild(
                    'product', [
                    'action'    =>  $action,
                    'id'    =>  $productId,
                    'type' => $itemType,
                    'storeid'   =>  $storeId
                ], false, $this->xmlGenerator->getSimpleXml()
                );

                $this->createChild('sku', false, $sku, $productElement);
                $this->createChild('id', false, $productId, $productElement);
            }
        }
    }

    /**
     * @param $productCollection
     */
    private function changePriceIndexJoinType($productCollection)
    {
        $updatedfromAndJoin = [];
        $fromAndJoin = $productCollection->getSelect()->getPart('FROM');
        foreach ($fromAndJoin as $key => $index) {
            if ($key == 'price_index') {
                $index['joinType'] = 'left join';
            }
            $updatedfromAndJoin[$key] = $index;
        }
        if (count($updatedfromAndJoin) > 0) {
            $productCollection->getSelect()->setPart('FROM', $updatedfromAndJoin);
        }

        $productCollection->clear();
    }

    /**
     * @param $product
     * @param $attrValue
     * @param $action
     */
    private function getAttrValue($product, $attrValue, $action)
    {
        $attrValidKey = $attrValue != null ? self::ISPKEY . $attrValue : self::ISPKEY;

        if (!array_key_exists($action, $this->attributesValuesCache)
            || !array_key_exists($attrValidKey, $this->attributesValuesCache[$action])
        ) {
            $attrValueText = $product->getAttributeText($action);
            if (is_array($attrValueText)) {
                $attrValueText = implode(',', $attrValueText);
            }
            if (!array_key_exists($action, $this->attributesValuesCache)) {
                $this->attributesValuesCache[$action] = [];
            }
            $this->attributesValuesCache[$action][$attrValidKey] = $attrValueText;
            $attrValue = $attrValueText;
        } else {
            $attrValueText = $this->attributesValuesCache[$action][$attrValidKey];

            $attrValue = $attrValueText;
        }
        return $attrValue;
    }

    public function base64JsonEncode($object) {
        return base64_encode(json_encode($object));
    }

    public function base64JsonDecode($string) {
        $result = json_decode(base64_decode($string), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return json_decode($string, true);
        }
        return $result;
    }

    public function getNextProductScheduledUpdateDateById($id) {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName($this->entityMetadata->getEntityTable());
        $select = $connection->select()
            ->from($tableName)
            ->where( $tableName . '.' . $this->entityMetadata->getIdentifierField() . ' = ' . $id .
                ' AND ' . $tableName . '.created_in > 1')
            ->setPart('disable_staging_preview', true);

        $updates = [];
        $rows = $connection->fetchAll($select);

        $nowDt = strtotime('now');
        $closestDate = $nowDt;

        $smallestStartDt = PHP_INT_MAX;
        $smallestEndDt = PHP_INT_MAX;
        $prevEndDt = 0;
        foreach ($rows as $r) {
            if ($r['created_in'] == $prevEndDt) {
                continue;
            }
            $createdIn = (int)$r['created_in'];
            if ($createdIn < $smallestStartDt && $createdIn > $nowDt) {
                $smallestStartDt = $createdIn;
            }
            $updatedIn = (int)$r['updated_in'];
            if ($updatedIn < $smallestStartDt && $updatedIn > $nowDt) {
                $smallestStartDt = $updatedIn;
            }
            $prevEndDt = $r['updated_in'];
        }
        if ($smallestStartDt > $nowDt) {
            $closestDate = $smallestStartDt;
        } elseif ($smallestEndDt > $nowDt) {
            $closestDate = $smallestEndDt;
        }
        if ($closestDate == PHP_INT_MAX) {
            $closestDate = false;
        }
        return $closestDate;
    }

    public function checkCachedAttrValues($store_id){
        $eAValuesCache = $this->cache->load(self::EAValuesCache . '_' . $store_id);
        if (!$eAValuesCache) {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName('eav_attribute_option_value'))
                ->where('store_id IN (?)', array(0, $store_id));
            $optionValuesResult = $connection->fetchAll($select);
            $optionValues = array();
            foreach ($optionValuesResult as $opV){
                if (($opV['store_id'] == $store_id) || !array_key_exists($opV['option_id'], $optionValues)) {
                    $optionValues[$opV['option_id']] = $opV['value'];
                }
            }

            if (count($optionValues)) {
                $this->cache->save(
                    $this->base64JsonEncode($optionValues),
                    self::EAValuesCache . '_' . $store_id,
                    ["autocomplete_cache"],
                    900
                );
            }
            $this->eAValues = $optionValues;
        } else {
            $this->eAValues = $this->base64JsonDecode($eAValuesCache);
        }
    }

    /**
     * @param $product
     * @param $dt
     */
    protected function updateScheduledUpsertsBuffer($product, $dt)
    {
        $scheduled = false;
        if ((!array_key_exists($product->getId(), $this->scheduledUpdatesBuffer)
            || $this->scheduledUpdatesBuffer[$product->getId()]['update_date'] > $dt)) {
            $this->scheduledUpdatesBuffer[$product->getId()] = [
                'product_id' => $product->getId(),
                'store_id' => $this->getStoreId(),
                'update_date' => $dt,
                'action' => 'update',
                'sku' => $product->getSku()
            ];
            $scheduled = true;
        }
        return $scheduled;
    }

    protected function flushUpdatesBuffer()
    {
        if (count($this->scheduledUpdatesBuffer) > 0) {
            $data = [];
            foreach ($this->scheduledUpdatesBuffer as $productId => $updateData) {
                $data[] = $updateData;
            }
            $this->batchesHelper->upsertData($data);
        }
    }
}
