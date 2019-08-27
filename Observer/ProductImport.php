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
use Magento\Setup\Exception;

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

    protected $_websites_stores_dict = [];

    protected $_product_batches_by_store = [];

    /**
     * DB data source model.
     *
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data
     */
    protected $_dataSourceModel;

    const MULTIPLE_INSERT_SIZE = 200;
    const MULTIPLE_UPDATE_SIZE = 150;

    /**
     * ProductSave constructor.
     *
     * @param \Autocompleteplus\Autosuggest\Helper\Data                                 $helper
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable              $configurable
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                               $date
     * @param \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory $batchCollectionFactory
     * @param \Magento\Catalog\Model\Product                                            $productModel
     * @param \Autocompleteplus\Autosuggest\Model\Batch                                 $batchModel
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                           $productRepositoryInterface
     * @param \Magento\Framework\Model\Context                                          $context
     * @param \Magento\Framework\Registry                                               $registry
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
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
    ) {
        $this->helper = $helper;
        $this->configurable = $configurable;

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/isp_import_debug.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        $this->date = $date;
        $this->batchCollectionFactory = $batchCollectionFactory;
        $this->productModel = $productModel;
        $this->batchModel = $batchModel;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->context = $context;
        $this->registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_resourceConnection = $resourceConnection;
        $this->_dataSourceModel = $importData;
        $this->getWebsitesStoreDict();
    }

    public function getBatchCollection()
    {
        $batchCollection = $this->batchCollectionFactory->create();
        $this->batchCollection = $batchCollection;

        return $this->batchCollection;
    }

    protected function getWebsitesStoreDict()
    {
        $connection = $this->_resourceConnection->getConnection();
        $table_name = $this->_resourceConnection->getTableName('store_group');
        $sql = $connection->select()
            ->from($table_name, '*')
            ->where(sprintf('%s.website_id != ?', $table_name), 0);
        $results = $connection->fetchAll($sql);
        foreach ($results as $row) {
            if (!array_key_exists($row['website_id'], $this->_websites_stores_dict)) {
                $this->_websites_stores_dict[$row['website_id']] = [];
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
        $product_websites = [];
        foreach ($results as $row) {
            $product_websites[] = $row['website_id'];
        }
        return $product_websites;
    }

    protected function getAllBatches()
    {
        $batchCollection = $this->getBatchCollection();
        $batchCollection->addFieldToSelect(['store_id', 'product_id']);
        foreach ($batchCollection as $batch) {
            if (!array_key_exists($batch['store_id'], $this->_product_batches_by_store)) {
                $this->_product_batches_by_store[$batch['store_id']] = [];
            }
            $this->_product_batches_by_store[$batch['store_id']][] = $batch['product_id'];
        }
    }

    /**
     * Update products
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = 0;
        $this->getAllBatches();
        $to_insert = [];
        $to_update = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $this->logger->info('enter into import observer with ' . count($bunch));
            foreach ($bunch as $itemArray) {
                $sku = $itemArray['sku'];
                $productId = $this->productModel->getIdBySku($sku);
                try {
                    $pWebsites = $this->getProductWebsites($productId);
                    $productStores = [];
                    foreach ($pWebsites as $websiteId) {
                        if (array_key_exists($websiteId, $this->_websites_stores_dict)) {
                            $productStores = array_merge($productStores, $this->_websites_stores_dict[$websiteId]);
                        }
                    }
                    //recording disabled item as deleted
                    if (array_key_exists('status', $itemArray) && $itemArray['status'] == '2') {
                        $this->helper->writeProductDeletionLight(
                            $sku,
                            $productId,
                            0,
                            $this->date->gmtTimestamp(),
                            $productStores
                        );
                        continue;
                    }

                } catch (\Exception $e) {

                    $this->logger->err($e->getMessage());
                    $productStores = [$storeId];
                }
                $parentProducts = $this->configurable->getParentIdsByChild($productId);
                foreach ($productStores as $productStoreId) {
                    $this->pushIntoUpdateBuffers($productStoreId, $productId, $to_insert, $to_update);
                    foreach ($parentProducts as $parrent) {
                        $this->pushIntoUpdateBuffers($productStoreId, $parrent, $to_insert, $to_update);
                    }
                }
            }
        }
        $this->logger->info('in import placed updates into buffers');
        $connection = $this->_resourceConnection->getConnection();
        $table_name = $this->_resourceConnection->getTableName('autosuggest_batch');
        $counter = 0;
        $data = [];

        try {
            foreach ($to_insert as $store => $productIds) {
                foreach ($productIds as $p_id) {
                    $counter++;
                    $data[] = [
                        'store_id' => (int)$store,
                        'product_id' => (int)$p_id,
                        'update_date' => (int)$this->date->gmtTimestamp() + $counter,
                        'action' => 'update'
                    ];
                    if ($counter == self::MULTIPLE_INSERT_SIZE) {
                        $connection->insertMultiple($table_name, $data);
                        $this->logger->info('executed multiple insert of ' . count($data));
                        $counter = 0;
                        $data = [];
                    }
                }

                if (count($data) > 0) {
                    $connection->insertMultiple($table_name, $data);
                    $this->logger->info('executed multiple insert of ' . count($data));
                    $counter = 0;
                    $data = [];
                }
            }

            foreach ($to_update as $p_id) {
                $counter++;
                $data[] = (int)$p_id;
                if ($counter == self::MULTIPLE_UPDATE_SIZE) {
                    $bind = ['update_date' => $this->date->gmtTimestamp(), 'action' => 'update'];
                    $where = [
                        'product_id IN (?)' => $data,
                    ];
                    $connection->update($table_name, $bind, $where);
                    $this->logger->info('executed multiple insert of ' . count($data));
                    $counter = 0;
                    $data = [];
                }
            }
            if (count($data) > 0) {
                $bind = ['update_date' => $this->date->gmtTimestamp(), 'action' => 'update'];
                $where = [
                    'product_id IN (?)' => $data,
                ];
                $connection->update($table_name, $bind, $where);
                $this->logger->info('executed multiple insert of ' . count($data));
            }

        } catch (\Exception $e) {
            $this->logger->err($e->getMessage());
        }

        return $this;
    }

    /**
     * @param $productStoreId
     * @param $productId
     * @param $to_insert
     * @param $to_update
     */
    protected function pushIntoUpdateBuffers($productStoreId, $productId, &$to_insert, &$to_update)
    {
        if (!array_key_exists($productStoreId, $this->_product_batches_by_store)) {
            if (!array_key_exists($productStoreId, $to_insert)) {
                $to_insert[$productStoreId] = [];
            }
            if (!in_array($productId, $to_insert[$productStoreId])) {
                $to_insert[$productStoreId][] = $productId;
            }
        } elseif (!in_array($productId, $this->_product_batches_by_store[$productStoreId])) {
            if (!array_key_exists($productStoreId, $to_insert)) {
                $to_insert[$productStoreId] = [];
            } elseif (in_array($productId, $to_insert[$productStoreId])) {
                return;
            }
            $to_insert[$productStoreId][] = $productId;
        } else {
            if (in_array($productId, $to_update)) {
                return;
            }
            $to_update[] = $productId;
        }
    }
}
