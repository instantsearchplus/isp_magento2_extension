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
     * @var \Autocompleteplus\Autosuggest\Helper\Batches
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Api
     */
    protected $apiHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * @param \Autocompleteplus\Autosuggest\Helper\Batches $helper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Autocompleteplus\Autosuggest\Helper\Api $apiHelper
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $helper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Magento\Catalog\Model\Product\Visibility $productVisibility
    ) {
        $this->helper = $helper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->apiHelper = $apiHelper;
        $this->productVisibility = $productVisibility;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();

        if ($category->isObjectNew() && $observer->getEvent()->getName() == 'catalog_category_save_before') {
            $this->ping_isp_server_on_new_category();
        }

        $products = $this->createProductsCollection($category);
        if (empty($products)) {
            return;
        }

        $store_products = $this->helper->groupProductIdsByStore($products);

        foreach ($store_products as $store_id => $ids) {
            if (count($ids) > 1000) {
                foreach (array_chunk($ids, 1000) as $chunk) {
                    $this->helper->writeMassProductsUpdate($chunk, $store_id);
                }
            } else {
                $this->helper->writeMassProductsUpdate($ids, $store_id);
            }
        }
    }

    protected function createProductsCollection($category): array
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addCategoryFilter($category);
        $collection->setVisibility($this->productVisibility->getVisibleInSearchIds());
        return array_map('intval', $collection->getAllIds());
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