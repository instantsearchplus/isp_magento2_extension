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

use Magento\Framework\Event\ObserverInterface;
use Magento\Setup\Exception;

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
    protected $stockFactory;
    protected $dateTime;
    protected $batchesHelper;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Batches $batchesHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\CatalogInventory\Model\Stock\Item $stockFactory
    ) {
        $this->batchesHelper = $batchesHelper;
        $this->dateTime = $dateTime;
        $this->stockFactory = $stockFactory;
        $this->_storeManager = $context->getStoreManager();
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $order_ids = $observer->getOrderIds();
        $orderItems = $order->getItems();
        $store_id = $this->_storeManager->getStore()->getId();
        $product_ids = [];
        foreach ($orderItems as $orderItem) {
            $productId = $orderItem->getProductId();
            $product_ids[] = $productId;
        }
        if (count($product_ids) > 0) {
            $this->batchesHelper->writeMassProductsUpdate($product_ids, $store_id);
        }
        return $this;
    }
}