<?php
/**
 * OrderCreate.php
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

use Autocompleteplus\Autosuggest\Helper\Api;
use Autocompleteplus\Autosuggest\Helper\Batches;
use Autocompleteplus\Autosuggest\Helper\Html\Injector;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class OrderCreate
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
class OrderCreate implements ObserverInterface
{
    /**
     * @var Item
     */
    protected $stockFactory;
    /**
     * @var DateTime
     */
    protected $dateTime;
    /**
     * @var Batches
     */
    protected $batchesHelper;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var Api
     */
    protected $apiHelper;
    /**
     * @var Injector
     */
    protected $injector_helper;
    /**
     * @var Session
     */
    protected $checkoutSession;
    protected $cartProduct;

    /**
     * @param Context $context
     * @param Batches $batchesHelper
     * @param DateTime $dateTime
     * @param Item $stockFactory
     * @param Api $api
     * @param Session $checkoutSession
     * @param Injector $injector_helper
     */
    public function __construct(
        Context  $context,
        Batches  $batchesHelper,
        DateTime $dateTime,
        Item     $stockFactory,
        Api      $api,
        Session  $checkoutSession,
        Injector $injector_helper
    )
    {
        $this->injector_helper = $injector_helper;
        $this->apiHelper = $api;
        $this->batchesHelper = $batchesHelper;
        $this->dateTime = $dateTime;
        $this->stockFactory = $stockFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product_ids = [];
        try {
            $order = $observer->getEvent()->getOrder();
            $orderItems = $order->getItems();
            $store_id = $this->_storeManager->getStore()->getId();

            foreach ($orderItems as $orderItem) {
                $productId = $orderItem->getProductId();
                $product_ids[] = $productId;
            }
            if (count($product_ids) > 0) {
                $this->batchesHelper->writeMassProductsUpdate($product_ids, $store_id);
            }

            $web_hook_url = $this->apiHelper->getApiEndpoint() . '/ma_webhook';
            $params = $this->_getWebhookObjectUri($observer);

            if ($params == null) {
                return;
            }

            if (function_exists('fsockopen')) {
                $this->apiHelper->post_without_wait(
                    $web_hook_url,
                    $params,
                    'POST'
                );
            } else {
                $this->apiHelper->setUrl($web_hook_url);
                $this->apiHelper->setRequestType(\Laminas\Http\Request::METHOD_POST);
                $response = $this->apiHelper->buildRequest($params);
            }

            $this->checkoutSession->setIspOrderSent(1);
        } catch (\Exception $e) {
            $this->apiHelper->sendError('Observer/OrderCreate | Exception: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        }

        return $this;
    }

    /**
     * Create the webhook URI.
     *
     * @return array
     */
    protected function _getWebhookObjectUri($observer)
    {
        $store_id = $this->_storeManager->getStore()->getId();
        $order = $observer->getEvent()->getOrder();
        $cart_items = $this->_getVisibleItemsFromOrder($order);
        $cart_token = $order->getQuoteID();
        $cart_products_json = json_encode($cart_items);
        $parameters = [
            'event' => 'success',
            'UUID' => $this->apiHelper->getApiUUID(),
            'key' => $this->apiHelper->getApiAuthenticationKey(),
            'store_id' => $store_id,
            'st' => $this->injector_helper->getSessionId(),
            'cart_token' => $cart_token,
            'serp' => '',
            'cart_product' => $cart_products_json,
        ];

        return $parameters;
    }

    protected function _getVisibleItemsFromOrder($order)
    {
        $orderItems = $order->getItems();
        return $this->_buildCartArray($orderItems, $order);
    }

    /**
     * Return a formatted array of quote or order items.
     *
     * @param array $cartItems
     *
     * @return array
     */
    protected function _buildCartArray($cartItems, $order)
    {
        $items = [];
        foreach ($cartItems as $item) {
            $quantity = $item->getQty();
            if ($quantity == null) {
                $quantity = $item->getqty_ordered();
            }
            if (is_object($item->getProduct())) {
                $this->cartProduct = $item->getProduct();
                $items[] = [
                    'product_id' => $item->getProduct()->getId(),
                    'price' => $item->getProduct()->getFinalPrice(),
                    'quantity' => $quantity,
                    'currency' => ($item->getQuote() == null) ?
                        $order->getorder_currency_code() :
                        $order->getGlobalCurrencyCode()
                ];
            }
        }

        return $items;
    }
}
