<?php
/**
 * Price.php
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
namespace Autocompleteplus\Autosuggest\Model\Plugin\Catalog\Index;

class Price
{
    protected $apiHelper;
    protected $batchesHelper;

    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $batchesHelper,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper
    ) {
        $this->apiHelper = $apiHelper;
        $this->batchesHelper = $batchesHelper;
    }

    /**
     * afterExecuteFull pings isp server after full price reindex
     * @param $subject
     * @return mixed
     */
    public function afterExecuteFull($subject)
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
            ];

            $response = $this->apiHelper->buildRequest($params);
        } catch (\Exception $e) {
        }
        return $subject;
    }

    /**
     * afterExecuteList records product updates into the batches table.
     * Magento calls executeList(array $ids), so the plugin receives that array
     * directly here.
     *
     * @param mixed $subject
     * @param mixed $result
     * @param array $ids
     * @return mixed
     */
    public function afterExecuteList($subject, $result, $ids = [])
    {
        try {
            if ($this->batchesHelper->getPluginDisabled()) {
                return $result;
            }

            if (!is_array($ids) || empty($ids)) {
                return $result;
            }

            $productIds = array_map('intval', $ids);
            $store_products = $this->batchesHelper->groupProductIdsByStore($productIds);

            foreach ($store_products as $store_id => $product_ids) {
                $this->batchesHelper->writeMassProductsUpdate($product_ids, $store_id);
            }
        } catch (\Exception $e) {
        }
        return $result;
    }
}
