<?php
/**
 * Webhook.php File
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
 * Webhook
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
class Webhook implements ObserverInterface
{
    protected $apiHelper;
    protected $injector_helper;
    protected $logger;
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected $_session;

    protected $orderRepository;

    protected $orderCollectionFactory;

    protected $cartProduct;

    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Batches
     */
    protected $batchesHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Api $api,
        \Autocompleteplus\Autosuggest\Helper\Html\Injector $injector_helper,
        \Magento\Checkout\Model\Session $session,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Autocompleteplus\Autosuggest\Helper\Batches $batchesHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->injector_helper = $injector_helper;
        $this->apiHelper = $api;
        $this->logger = $context->getLogger();
        $this->_storeManager = $context->getStoreManager();
        $this->_session = $session;
        $this->orderRepository = $orderRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->batchesHelper = $batchesHelper;
        $this->date = $date;
    }

    /**
     * Format visible cart contents into a multidimensional keyed array.
     *
     * @return array
     */
    protected function _getVisibleItems()
    {
        $cartItems = $this->_session->getQuote()->getAllVisibleItems();
        return $this->_buildCartArray($cartItems);
    }

    protected function _getVisibleItemsFromOrder($order)
    {
        $orderItems = $order->getItems();
        return $this->_buildCartArray($orderItems);
    }

    /**
     * Return a formatted array of quote or order items.
     *
     * @param array $cartItems
     *
     * @return array
     */
    protected function _buildCartArray($cartItems)
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
                    'currency' => ($item->getQuote() == null)?
                        $item->getOrder()->getorder_currency_code() :
                        $item->getQuote()->getGlobalCurrencyCode()
                ];
            }
        }

        return $items;
    }

    public function getOrder($id)
    {
        $tempOrdCollection = $this->orderCollectionFactory->create();
        $tempOrdCollection->addAttributeToFilter('entity_id', ['in'  =>  [$id]]);
        $order = $tempOrdCollection->getFirstItem();
        return $order;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $eventName = $observer->getEvent()->getName();
            $web_hook_url = $this->apiHelper->getApiEndpoint() . '/ma_webhook';
            $params = $this->_getWebhookObjectUri($eventName, $observer);
            
            if ($params == null) {
                return;
            }
            
            if (function_exists('fsockopen')) {
                $this->post_without_wait(
                    $web_hook_url,
                    $params,
                    'GET'
                );
            } else {
                $this->apiHelper->setUrl($web_hook_url);
                $this->apiHelper->setRequestType(\Zend_Http_Client::GET);
                $response = $this->apiHelper->buildRequest($params);
            }

        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * post_without_wait send http call and close the connection without waiting for response
     *
     * @param $url
     * @param array $params
     * @param string $type
     *
     * @return void
     */
    private function post_without_wait($url, $params = [], $type = 'POST', $post_params = [])
    {
        foreach ($params as $key => &$val) {
            if (is_array($val)) {
                $val = implode(',', $val);
            }
            $post_params[] = $key.'='.urlencode($val);
        }

        $post_string = implode('&', $post_params);
        $parts=parse_url($url);
        $fp = fsockopen(
            $parts['host'],
            isset($parts['port'])? $parts['port'] : 80,
            $errno,
            $errstr,
            30
        );

        // Data goes in the path for a GET request
        if ('GET' == $type) {
            $parts['path'] .= '?'.$post_string;
        }

        $out = "$type ".$parts['path']." HTTP/1.1\r\n";
        $out.= "Host: ".$parts['host']."\r\n";

        if ($type == 'POST') {
            $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out.= "Content-Length: ".strlen($post_string)."\r\n";
        }

        $out.= "Connection: Close\r\n\r\n";
        // Data goes in the request body for a POST request
        if ('POST' == $type && isset($post_string)) {
            $out.= $post_string;
        }

        fwrite($fp, $out);
        fclose($fp);
    }

    /**
     * Create the webhook URI.
     *
     * @return string
     */
    protected function _getWebhookObjectUri($event_name, $observer)
    {
        $store_id = $this->_storeManager->getStore()->getId();
        if ($event_name == 'checkout_cart_add_product_complete') {
            $cart_items = $this->_getVisibleItems();
            $cart_token = $this->_session->getQuote()->getID();
        } elseif ($event_name == 'checkout_onepage_controller_success_action') {
            $order_ids = $observer->getOrderIds();
            if ($order_ids == null || count($order_ids) == 0) {
                return null;
            }
            $order = $this->getOrder($order_ids[0]);
            $cart_items = $this->_getVisibleItemsFromOrder($order);
            $cart_token = $order->getQuoteID();

            if ($this->cartProduct) {
                $dt = $this->date->gmtTimestamp();
                if (($this->cartProduct->getTypeId() == 'simple' && !$this->cartProduct->isSalable())
                    || $this->cartProduct->getTypeId() == 'configurable') {
                    $this->batchesHelper->writeProductUpdate(
                        $this->cartProduct,
                        $this->cartProduct->getId(),
                        $store_id,
                        $dt,
                        $this->cartProduct->getSku()
                    );
                }
            }
            //TODO: implement product removal when qty = 0 and show_out_of_stock_items = false
        }
        $cart_products_json = json_encode($cart_items);
        $parameters = [
            'event' => $this->getWebhookEventLabel($event_name),
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

    /**
     * Return a label for webhooks based on the current
     * controller route. This cannot be handled by layout
     * XML because the layout engine may not be init in all
     * future uses of the webhook.
     *
     * @return string|void
     */
    public function getWebhookEventLabel($event_name)
    {
        switch ($event_name) {
            case 'checkout_cart_add_product_complete':
                return 'cart';
            case 'controller_action_postdispatch_checkout_onepage_index':
                return 'checkout';
            case 'checkout_onepage_controller_success_action':
                return 'success';
            default:
                return null;
        }
    }
}
