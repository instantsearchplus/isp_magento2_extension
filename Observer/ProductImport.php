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
     * Catalog helper
     *
     * @var \Magento\Catalog\Helper\Catalog
     */
    protected $helper;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory
     */
    protected $batchCollectionFactory;

    protected $productModel;

    protected $batchModel;

    protected $productRepositoryInterface;

    protected $context;

    protected $registry;
    /**
     * @var \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\Collection
     */
    protected $batchCollection;

    protected $_storeManager;

    protected $_resourceConnection;

    protected $_websites_stores_dict = array();
    /**
     * ProductSave constructor.
     * @param \Autocompleteplus\Autosuggest\Helper\Data $helper
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory $batchCollectionFactory
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Autocompleteplus\Autosuggest\Model\Batch $batchModel
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $helper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory $batchCollectionFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Autocompleteplus\Autosuggest\Model\Batch $batchModel,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreRepository $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->helper = $helper;
        $this->configurable = $configurable;
        $this->logger = $context->getLogger();
        $this->date = $date;
        $this->batchCollectionFactory = $batchCollectionFactory;
        $this->productModel = $productModel;
        $this->batchModel = $batchModel;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->context = $context;
        $this->registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_resourceConnection = $resourceConnection;
        $this->getWebsitesStoreDict();
    }

    public function getBatchCollection()
    {
        $batchCollection = $this->batchCollectionFactory->create();
        $this->batchCollection = $batchCollection;

        return $this->batchCollection;
    }

    protected function getWebsitesStoreDict() {
        $connection = $this->_resourceConnection->getConnection();
        $table_name = $this->_resourceConnection->getTableName('store_group');
        $sql = $connection->select()
            ->from($table_name, '*')
            ->where(sprintf('%s.website_id != ?', $table_name), 0);
        $results = $connection->fetchAll($sql);
        foreach ($results as $row) {
            if (!array_key_exists($row['website_id'], $this->_websites_stores_dict)) {
                $this->_websites_stores_dict[$row['website_id']] = array();
            }
            $this->_websites_stores_dict[$row['website_id']][] = $row['group_id'];
        }
    }

    protected function getProductWebsites($productId)
    {
        $connection = $this->_resourceConnection->getConnection();
        $table_name = $this->_resourceConnection->getTableName('catalog_product_website');
        $sql = $connection->select()
            ->from($table_name, '*')
            ->where(sprintf('%s.product_id = ?', $table_name), $productId);
        $results = $connection->fetchAll($sql);
        $product_websites = array();
        foreach ($results as $row) {
            $product_websites[] = $row['website_id'];
        }
        return $product_websites;
    }

    /**
     * Update products
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $bunch = $observer->getEvent()->getBunch();
        $storeId = 0;
        foreach ($bunch as $itemArray) {
            $sku = $itemArray['sku'];
            $productId = $this->productModel->getIdBySku($sku);

            $dt = $this->date->gmtTimestamp();
            try {
                try {
                    $pWebsites = $this->getProductWebsites($productId);
                    $productStores = array();
                    foreach ($pWebsites as $websiteId) {
                        if (array_key_exists($websiteId, $this->_websites_stores_dict)) {
                            $productStores = array_merge($productStores, $this->_websites_stores_dict[$websiteId]);
                        }
                    }
                    //recording disabled item as deleted
                    if (array_key_exists('status', $itemArray) && $itemArray['status'] == '2') {
                        $this->helper->writeProductDeletionLight($sku, $productId, 0, $dt, $productStores);
                        continue;
                    }

                } catch (\Exception $e) {

                    $this->logger->critical($e);
                    $productStores = [$storeId];
                }

                $simpleProducts = $this->configurable->getParentIdsByChild($productId);
                foreach ($productStores as $productStore) {
                    $batches = $this->getBatchCollection()
                        ->addFieldToFilter('product_id', $productId)
                        ->addFieldToFilter('store_id', $productStore)
                        ->setPageSize(1);

                    if ($batches->getSize() > 0) {
                        $batch = $batches->getFirstItem();
                        $batch->setUpdateDate($dt)
                            ->setAction('update')
                            ->setProductId($productId)
                            ->setStoreId($productStore)
                            ->save();
                    } else {
                        $batch = new \Autocompleteplus\Autosuggest\Model\Batch($this->context, $this->registry);
                        $batch->setUpdateDate($dt)
                            ->setAction('update')
                            ->setProductId($productId)
                            ->setStoreId($productStore)
                            ->setSku($sku)
                            ->save();
                    }

                    if (is_array($simpleProducts) && count($simpleProducts) > 0) {
                        foreach ($simpleProducts as $configurableProduct) {
                            $batchCollection = $this->getBatchCollection();
                            $batchCollection->addFieldToFilter(
                                'product_id',
                                $configurableProduct
                            )
                                ->addFieldToFilter('store_id', $productStore)
                                ->setPageSize(1);

                            if ($batchCollection->getSize() > 0) {
                                $batch = $batchCollection->getFirstItem();
                                if ($batch->getAction() !== 'remove') {
                                    $batch->setUpdateDate($dt)
                                        ->setAction('update')
                                        ->setProductId($configurableProduct)
                                        ->setStoreId($productStore)
                                        ->save();
                                }
                            } else {
                                $batch = new \Autocompleteplus\Autosuggest\Model\Batch($this->context, $this->registry);

                                $batch->setUpdateDate($dt)
                                    ->setProductId($configurableProduct)
                                    ->setAction('update')
                                    ->setStoreId($productStore)
                                    ->setSku('ISP_NO_SKU')
                                    ->save();
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        return $this;
    }
}