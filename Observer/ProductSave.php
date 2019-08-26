<?php
/**
 * ProductSave File
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
 * ProductSave
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
class ProductSave implements ObserverInterface
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
     * ProductSave constructor.
     *
     * @param \Autocompleteplus\Autosuggest\Helper\Data   $helper
     * @param \Psr\Log\LoggerInterface                    $logger
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->date = $date;
    }

    /**
     * Update products
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $origData = $product->getOrigData();
        $storeId = $product->getStoreId();
        $productId = $product->getId();
        $sku = $product->getSku();
        $dt = $this->date->gmtTimestamp();
        if (is_array($origData) 
            && array_key_exists('sku', $origData)
        ) {
            $oldSku = $origData['sku'];
            if ($sku != $oldSku) {
                $this->helper->writeProductDeletion($oldSku, $productId, 0, $dt, $product);
            }
        }

        //recording disabled item as deleted
        if ($product->getStatus() == '2') {
            $this->helper->writeProductDeletion($sku, $productId, 0, $dt, $product);
            return $this;
        }

        $this->helper->writeProductUpdate($product, $productId, $storeId, $dt, $sku);

        return $this;
    }
}
