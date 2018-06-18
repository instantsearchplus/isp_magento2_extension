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
        \Magento\Framework\Registry $registry
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
    }

    public function getBatchCollection()
    {
        $batchCollection = $this->batchCollectionFactory->create();
        $this->batchCollection = $batchCollection;

        return $this->batchCollection;
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
                    $product = $this->productRepositoryInterface->getById($productId);
                    //recording disabled item as deleted
                    if ($product->getStatus() == '2') {
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