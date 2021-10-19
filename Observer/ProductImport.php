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
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    protected $productModel;

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
     * @param \Magento\Catalog\Model\Product                                            $productModel
     * @param \Magento\Framework\Model\Context                                          $context
     * @param \Magento\Framework\Registry                                               $registry
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $helper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreRepository $storeManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
    ) {
        $this->helper = $helper;
        $this->configurable = $configurable;
        $this->date = $date;
        $this->productModel = $productModel;
        $this->context = $context;
        $this->registry = $registry;
        $this->_storeManager = $storeManager;
        $this->_resourceConnection = $resourceConnection;
        $this->_dataSourceModel = $importData;
        $this->getWebsitesStoreDict();
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

    /**
     * Update products
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = 0;
        $to_update = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
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
                    $productStores = [$storeId];
                }
                $parentProducts = $this->configurable->getParentIdsByChild($productId);
                $dt = $this->date->gmtTimestamp();
                foreach ($productStores as $productStoreId) {
                    $to_update[] = [
                        'product_id'=> $productId,
                        'store_id'=> $productStoreId,
                        'update_date'=> $dt,
                        'action'=> 'update',
                        'sku'=> 'ISP_NO_SKU'
                    ];
                    foreach ($parentProducts as $parrentId) {
                        $to_update[] = [
                            'product_id'=> $parrentId,
                            'store_id'=> $productStoreId,
                            'update_date'=> $dt,
                            'action'=> 'update',
                            'sku'=> 'ISP_NO_SKU'
                        ];
                    }
                }
            }
        }
        $connection = $this->_resourceConnection->getConnection();
        $table_name = $this->_resourceConnection->getTableName('autosuggest_batch');
        $counter = 0;
        $data = [];

        try {
            if (count($to_update) > 0) {
                $connection->insertOnDuplicate($table_name, $to_update);
            }
        } catch (\Exception $e) {
        }

        return $this;
    }
}
