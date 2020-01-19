<?php
/**
 * CategorySave.php
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
 * @copyright 2019 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class CategorySave
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
class CategorySave implements ObserverInterface
{
    /**
     * Catalog helper
     *
     * @var \Autocompleteplus\Autosuggest\Helper\Batches
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\Collection
     */
    protected $batchCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * CategorySave constructor.
     * @param \Autocompleteplus\Autosuggest\Helper\Batches $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  $productCollectionFactory
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->date = $date;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();

        $products = $this->createProductsCollection($category);
        $store_products = [];

        foreach ($products as $product) {
            $store_ids = $product->getStoreIds();
            foreach ($store_ids as $store_id) {
                if (!array_key_exists($store_id, $store_products)) {
                    $store_products[$store_id] = [];
                }
                $store_products[$store_id][] = (int)$product->getId();
            }
        }

        foreach ($store_products as $store_id => $products) {
            if (count($products) > 1000) {
               $chunks = array_chunk($products, 1000);
               foreach ($chunks as $chunk) {
                   $this->helper->writeMassProductsUpdate($chunks, $store_id);
               }
            } else {
                $this->helper->writeMassProductsUpdate($products, $store_id);
            }
        }
    }

    /**
     * @param $category
     */
    protected function createProductsCollection($category)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addCategoryFilter($category);
        $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        return $collection;
    }
}