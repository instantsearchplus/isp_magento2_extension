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

    protected $apiHelper;

    /**
     * CategorySave constructor.
     * @param \Autocompleteplus\Autosuggest\Helper\Batches $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  $productCollectionFactory,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper
    ) {
        $this->helper = $helper;
        $this->date = $date;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->apiHelper = $apiHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();

        if ($observer->getEvent()->getName() == 'catalog_category_delete_after_done') {
            $category = $observer->getProduct();
        }

        if (($category->isObjectNew() && $observer->getEvent()->getName() == 'catalog_category_save_before')
            || $category->isDeleted()
        ) {
            $this->ping_isp_server_on_new_category();
        }

        $products = $this->createProductsCollection($category);
        $store_products = [];

        foreach ($products as $product_id) {
            $store_ids = $this->helper->getProductStoresById($product_id);
            foreach ($store_ids as $store_id) {
                if (!array_key_exists($store_id, $store_products)) {
                    $store_products[$store_id] = [];
                }
                $store_products[$store_id][] = $product_id;
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
        $collection = $this->helper->getCategoryProducts($category->getId());
        return $collection;
    }

    protected function ping_isp_server_on_new_category()
    {
        try {
            $auth_key = $this->apiHelper->getApiAuthenticationKey();
            $uuid = $this->apiHelper->getApiUUID();
            $web_hook_url = $this->apiHelper->getApiEndpoint() . '/reindex_after_update_catalog';
            $this->apiHelper->setUrl($web_hook_url);

            $params = [
                'isp_platform' => 'magento',
                'auth_key' => $auth_key,
                'uuid' => $uuid,
                'update_type' => 'categories'
            ];

            $response = $this->apiHelper->buildRequest($params);
        } catch (\Exception $e) {
        }
    }
}