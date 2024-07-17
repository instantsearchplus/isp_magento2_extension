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

use Autocompleteplus\Autosuggest\Helper\Api;
use Autocompleteplus\Autosuggest\Helper\Batches;
use Autocompleteplus\Autosuggest\Helper\Html\Injector;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

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
    /**
     * @var Api
     */
    protected $apiHelper;

    /**
     * @var Injector
     */
    protected $injector_helper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    protected $cartProduct;

    /**
     * @var Batches
     */
    protected $batchesHelper;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @param Context $context
     * @param Api $api
     * @param Injector $injector_helper
     * @param Session $session
     * @param OrderRepositoryInterface $orderRepository
     * @param CollectionFactory $orderCollectionFactory
     * @param Batches $batchesHelper
     * @param DateTime $date
     */
    public function __construct(
        Context                  $context,
        Api                      $api,
        Injector                 $injector_helper,
        Session                  $session,
        OrderRepositoryInterface $orderRepository,
        CollectionFactory        $orderCollectionFactory,
        Batches                  $batchesHelper,
        DateTime                 $date
    )
    {
        $this->injector_helper = $injector_helper;
        $this->apiHelper = $api;
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
                    'currency' => ($item->getQuote() == null) ?
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
        $tempOrdCollection->addAttributeToFilter('entity_id', ['in' => [$id]]);
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

        } catch (\Exception $e) {
            $this->apiHelper->sendError('Observer/Webhook | Exception: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Create the webhook URI.
     *
     * @return array
     */
    protected function _getWebhookObjectUri($event_name, $observer)
    {
        $store_id = $this->_storeManager->getStore()->getId();
        if ($event_name == 'checkout_cart_add_product_complete') {
            $cart_items = $this->_getVisibleItems();
            $cart_token = $this->_session->getQuote()->getID();
        } elseif ($event_name == 'checkout_onepage_controller_success_action') {
            if ($this->_session->getIspOrderSent() == 1) {
                $this->_session->unsIspOrderSent();
                return null;
            }
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
                    || $this->cartProduct->getTypeId() == 'configurable'
                ) {
                    $this->batchesHelper->writeProductUpdate(
                        $this->cartProduct,
                        $this->cartProduct->getId(),
                        $store_id,
                        $dt,
                        $this->cartProduct->getSku()
                    );
                }
            }
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
