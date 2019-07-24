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
     * @var \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\Collection
     */
    protected $batchCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    protected $objectManager;

    const MULTIPLE_INSERT_SIZE = 200;

    public function __construct(
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
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
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function getBatchCollection()
    {
        $batchCollection = $this->batchCollectionFactory->create();
        $this->batchCollection = $batchCollection;

        return $this->batchCollection;
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

            $simpleProducts = [];
            if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
                $simpleProducts = $this->configurable->getParentIdsByChild($product->getId());
            }

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
                    $batch = $this->objectManager->create('\Autocompleteplus\Autosuggest\Model\Batch');
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
                            $batch = $this->objectManager->create('\Autocompleteplus\Autosuggest\Model\Batch');
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

    /**
     * @param $sku
     * @param $productId
     * @param $storeId
     * @param null $product
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

        $this->removeProductForEachStore($sku, $productId, $dt, $productStores);
    }

    /**
     * @param $sku
     * @param $productId
     * @param $storeId
     * @param null $product
     */
    public function writeProductDeletionLight($sku, $productId, $storeId, $dt, $productStores = null)
    {
        if (!$productStores) {
            $productStores = [$storeId];
        }

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
        if ($sku == null) {
            $sku = 'dummy_sku';
        }

        foreach ($productStores as $productStore) {
            $batchCollection = $this->getBatchCollection();
            $batchCollection->addFieldToFilter('product_id', $productId)
                ->addFieldToFilter('store_id', $productStore)
                ->setPageSize(1);

            if ($batchCollection->getSize() > 0) {
                $batch = $batchCollection->getFirstItem();
                $batch->setUpdateDate($dt)
                    ->setAction('remove')
                    ->setProductId($productId)
                    ->setStoreId($productStore)
                    ->save();
            } else {
                $batch = $this->objectManager->create('\Autocompleteplus\Autosuggest\Model\Batch');
                $batch->setUpdateDate($dt)
                    ->setAction('remove')
                    ->setProductId($productId)
                    ->setStoreId($productStore)
                    ->setSku($sku)
                    ->save();
            }
        }
    }

    /**
     * @param $product_ids
     * @param $rows
     */
    public function writeMassProductsUpdate($products_ids, $store_id) {
        $connection = $this->_resourceConnection->getConnection();
        $table_name = $this->_resourceConnection->getTableName('autosuggest_batch');
        $counter = 0;
        $where = [
            'product_id IN (?)' => $products_ids,
            'store_id = ?' => $store_id
        ];

        $connection->delete($table_name, $where);

        $data = [];
        foreach ($products_ids as $p_id) {
            $counter++;
            $data[] = [
                'store_id' => (int)$store_id,
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
        }
    }
}
