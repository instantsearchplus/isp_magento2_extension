<?php
/**
 * ProductSave File
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

namespace Autocompleteplus\Autosuggest\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * ProductSave
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
class ProductSave implements ObserverInterface
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

    /**
     * @var \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\Collection
     */
    protected $batchCollection;

    /**
     * @param \Magento\Catalog\Helper\Catalog $helperCatalog
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory $batchCollectionFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Autocompleteplus\Autosuggest\Model\Batch $batchModel
    ) {
        $this->helper = $helper;
        $this->configurable = $configurable;
        $this->logger = $logger;
        $this->date = $date;
        $this->batchCollectionFactory = $batchCollectionFactory;
        $this->productModel = $productModel;
        $this->batchModel = $batchModel;
    }

    public function getBatchCollection()
    {
        if (!$this->batchCollection) {
            $batchCollection = $this->batchCollectionFactory->create();
            $this->batchCollection = $batchCollection;
        }

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
        $product = $observer->getEvent()->getProduct();
        $origData = $product->getOrigData();
        $storeId = $product->getStoreId();
        $productId = $product->getId();
        $sku = $product->getSku();
        if (is_array($origData) &&
            array_key_exists('sku', $origData)) {
            $oldSku = $origData['sku'];
            if ($sku != $oldSku) {
                $this->helper->writeProductDeletion($oldSku, $productId, $storeId, $product);
            }
        }

        $dt = $this->date->gmtTimestamp();
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
                    $batch = $this->batchModel;
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
                            $batch = $this->batchModel;

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

        return $this;
    }
}
