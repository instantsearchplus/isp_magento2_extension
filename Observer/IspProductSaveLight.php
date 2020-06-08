<?php
/**
 * IspProductSaveLight.php
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
 * @copyright 2020 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class IspProductSaveLight
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
class IspProductSaveLight implements ObserverInterface
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
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->helper = $helper;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/isp_ma_search_debug.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
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
        $productIds = $observer->getEvent()->getProductIds();
        $this->logger->info('updating items:');
        $this->logger->info(print_r($productIds, true));

        $storesProductsData = array();
        foreach ($productIds as $productId) {
            $productStores = $this->helper->getProductStoresById($productId);
            foreach ($productStores as $storeId) {
               if (!array_key_exists($storeId, $storesProductsData)) {
                   $storesProductsData[$storeId] = array();
               }
               $storesProductsData[$storeId][] = $productId;
            }
        }

        foreach ($storesProductsData as $storeId => $productIdByStore) {
            $this->helper->writeMassProductsUpdate($productIdByStore, $storeId);
        }

        return $this;
    }
}
