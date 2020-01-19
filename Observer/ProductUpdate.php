<?php
/**
 * ProductUpdate File
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
 * ProductUpdate
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
class ProductUpdate implements ObserverInterface
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

    protected $productRepositoryInterface;

    protected $context;

    protected $registry;
    /**
     * @var \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\Collection
     */
    protected $batchCollection;

    protected $_resourceConnection;

    /**
     * ProductSave constructor.
     *
     * @param \Autocompleteplus\Autosuggest\Helper\Data                                 $helper
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable              $configurable
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                               $date
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                           $productRepositoryInterface
     * @param \Magento\Framework\Model\Context                                          $context
     * @param \Magento\Framework\Registry                                               $registry
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $helper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->configurable = $configurable;
        $this->logger = $context->getLogger();
        $this->date = $date;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->context = $context;
        $this->registry = $registry;
        $this->_resourceConnection = $resourceConnection;
    }

    /**
     * Update products
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $bunch = $observer->getEvent()->getProductIds();
        $attributes_data = $observer->getEvent()->getAttributesData();
        $storeId = 0;
        $data = [];
        $counter = 0;
        foreach ($bunch as $productIdStr) {
            $productId = (int)$productIdStr;
            $dt = $this->date->gmtTimestamp();
            try {
                try {
                    $product = $this->productRepositoryInterface->getById($productId);
                    $sku = $product->getSku();
                    //recording disabled item as deleted
                    if (($product->getStatus() == '2' && !array_key_exists('status', $attributes_data))
                        || (array_key_exists('status', $attributes_data) && $attributes_data['status'] == 2)
                    ) {
                        $this->helper->writeProductDeletion($sku, $productId, 0, $dt, $product);
                        continue;
                    }
                    $productStores = ($storeId == 0 && method_exists($product, 'getStoreIds'))
                        ? $product->getStoreIds() : [$storeId];
                } catch (\Exception $e) {

                    $this->logger->critical($e);
                    $productStores = [$storeId];
                }

                $simpleProducts = [];
                if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
                    $simpleProducts = $this->configurable->getParentIdsByChild($product->getId());
                }

                foreach ($productStores as $productStore) {
                    $counter++;
                    $data[] = [
                        'store_id' => (int)$productStore,
                        'product_id' => (int)$productId,
                        'update_date' => (int)$this->date->gmtTimestamp() + $counter,
                        'action' => 'update'
                    ];

                    if (is_array($simpleProducts) && count($simpleProducts) > 0) {
                        foreach ($simpleProducts as $configurableProduct) {
                            $counter++;
                            $data[] = [
                                'store_id' => (int)$productStore,
                                'product_id' => (int)$configurableProduct,
                                'update_date' => (int)$this->date->gmtTimestamp() + $counter,
                                'action' => 'update'
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
        $connection = $this->_resourceConnection->getConnection();
        $table_name = $this->_resourceConnection->getTableName('autosuggest_batch');
        $connection->insertOnDuplicate($table_name, $data);
        return $this;
    }
}
