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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @param \Autocompleteplus\Autosuggest\Helper\Batches $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->helper = $helper;
        $this->logger = $context->getLogger();
        $this->date = $date;
        $this->storeManager = $storeManagerInterface;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Update products
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $bunch = $observer->getEvent()->getProductIds();
            if (empty($bunch)) {
                return $this;
            }
            $productIds = array_map('intval', (array)$bunch);
            $attributes_data = $observer->getEvent()->getAttributesData() ?: [];
            $storeId = (int)$this->storeManager->getStore()->getId();

            $this->helper->setPluginDisabled(true);

            $productStoresMap = ($storeId == 0)
                ? $this->helper->getProductStoresByIds($productIds)
                : [];
            $parentIdsMap = $this->helper->getParentIdsByChildren($productIds);

            $collection = $this->productCollectionFactory->create();
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
            $collection->addAttributeToSelect('status');

            $dt = $this->date->gmtTimestamp();
            $data = [];
            $counter = 0;
            $massDisabling = array_key_exists('status', $attributes_data) && $attributes_data['status'] == 2;

            foreach ($collection as $product) {
                $productId = (int)$product->getId();
                $isDisabled = $massDisabling
                    || ($product->getStatus() == 2 && !array_key_exists('status', $attributes_data));

                $productStores = ($storeId == 0)
                    ? ($productStoresMap[$productId] ?? [$storeId])
                    : [$storeId];

                if ($isDisabled) {
                    foreach ($productStores as $productStore) {
                        $data[] = [
                            'store_id'   => (int)$productStore,
                            'product_id' => $productId,
                            'update_date'=> $dt,
                            'action'     => 'remove',
                            'sku'        => 'ISP_NO_SKU',
                        ];
                    }
                    continue;
                }

                $parentIds = $parentIdsMap[$productId] ?? [];
                foreach ($productStores as $productStore) {
                    $counter++;
                    $data[] = [
                        'store_id'   => (int)$productStore,
                        'product_id' => $productId,
                        'update_date'=> $dt + $counter,
                        'action'     => 'update',
                        'sku'        => 'ISP_NO_SKU',
                    ];
                    foreach ($parentIds as $parentId) {
                        $counter++;
                        $data[] = [
                            'store_id'   => (int)$productStore,
                            'product_id' => (int)$parentId,
                            'update_date'=> $dt + $counter,
                            'action'     => 'update',
                            'sku'        => 'ISP_NO_SKU',
                        ];
                    }
                }
            }

            if (count($data) > 0) {
                $this->helper->upsertData($data);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $this;
    }
}
