<?php
/**
 * ProductDelete File
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
 * ProductDelete
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
class ProductDelete implements ObserverInterface
{
    /**
     * Autocompleteplus helper
     *
     * @var \Autocompleteplus\Autosuggest\Helper\Data
     */
    protected $helper;

    /**
     * Magento configurable product type model
     *
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * Logging interface
     *
     * @var \Psr\Logger\LoggerInterface
     */
    protected $logger;

    protected $batchCollection;

    /**
     * @param \Autocompleteplus\Autosuggest\Helper\Data $helper
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Psr\Logger\LoggerInterface $logger
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->configurable = $configurable;
        $this->logger = $logger;
    }

    /**
     * Mark product for deletion
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $storeId = $product->getStoreId();
        $productId = $product->getId();
        $sku = $product->getSku();

        $this->helper->writeProductDeletion($sku, $productId, $storeId, $product);
        return $this;
    }
}
