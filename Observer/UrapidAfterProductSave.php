<?php
/**
 * UrapidAfterProductSave.php
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
 * Class UrapidAfterProductSave
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
class UrapidAfterProductSave implements ObserverInterface
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
     * @param \Autocompleteplus\Autosuggest\Helper\Data                                 $helper
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable              $configurable
     * @param \Psr\Log\LoggerInterface                                                  $logger
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                               $date
     * @param \Autocompleteplus\Autosuggest\Model\ResourceModel\Batch\CollectionFactory $batchCollectionFactory
     * @param \Magento\Catalog\Model\Product                                            $productModel
     * @param \Autocompleteplus\Autosuggest\Model\Batch                                 $batchModel
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
        $vars = $observer->getEvent()->getVars();
        $skus = $vars['skus'];
        $profile = $vars['profile'];
        $store_id = $profile->getStoreId();
        $product_ids = [];
        foreach ($skus as $sku => $productId) {
            $product_ids[] = (int)$productId;
        }

        $this->helper->writeMassProductsUpdate($product_ids, $store_id);
        return $this;
    }
}
