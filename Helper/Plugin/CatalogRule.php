<?php
/**
 * CatalogRule.php
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
 * @copyright 2018 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Helper\Plugin;

use Magento\Framework\App\Helper;
use Magento\CatalogRule\Api\Data;


class CatalogRule
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

    protected $dataHelper;

    protected $storeManager;

    /**
     * CatalogRule constructor.
     * @param \Autocompleteplus\Autosuggest\Helper\Batches $helper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Autocompleteplus\Autosuggest\Helper\Data $dataHelper
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Autocompleteplus\Autosuggest\Helper\Data $dataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->date = $date;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $rule
     * @return mixed
     */
    public function aroundSave($subject, $proceed, $rule)
    {
        $result = $proceed($rule);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        try {
            $isp_rule = $objectManager->create('Autocompleteplus\Autosuggest\Model\Rule')->load($rule->getRuleId());
        } catch (\Exception $e) {
            return $result;
        }
        $dtNow = $this->date->gmtTimestamp();
        $dtFrom = null;
        $dtTo = null;
        $dt = null;

        if ($rule->getFromDate()) {
            $localFromDate = new \DateTime($rule->getFromDate(), new \DateTimeZone(
                    $this->dataHelper->getTimezone($this->storeManager->getStore()->getId())
                )
            );
            $dtFrom = $localFromDate->getTimestamp();
        }

        if (!$dt && $rule->getToDate()) {
            $localToDate = new \DateTime($rule->getToDate(), new \DateTimeZone(
                    $this->dataHelper->getTimezone($this->storeManager->getStore()->getId())
                )
            );
            $dtTo = $localToDate->getTimestamp();
            $dtTo = strtotime("tomorrow", $dtTo);
        }

        if (!$dtFrom && !$dtTo) {
            $dt = $dtNow;
        } elseif (!$dtFrom && $dtTo <= $dtNow) {
            return $result;
        } elseif (!$dtFrom && $dtTo >= $dtNow) {
            $dt = $dtNow;
        } elseif ($dtFrom < $dtNow && !$dtTo) {
            $dt = $dtNow;
        } elseif ($dtFrom < $dtNow && $dtTo <= $dtNow) {
            return $result;
        } elseif ($dtFrom < $dtNow && $dtTo >= $dtNow) {
            $dt = $dtNow;
        } elseif ($dtFrom >= $dtNow && !$dtTo) {
            $dt = $dtFrom;
        } elseif ($dtFrom >= $dtNow && $dtTo <= $dtNow) {
            return $result;
        } elseif ($dtFrom >= $dtNow && $dtTo >= $dtNow) {
            $dt = $dtFrom;
        }

        $affectedProducts = $isp_rule->getMatchingProductIds();
        foreach ($affectedProducts as $product_id => $product) {
            $this->helper->writeProductUpdate(
                $product[0],
                $product[0]->getId(),
                $product[0]->getStoreId(),
                $dt,
                $product[0]->getSku()
            );
        }
        return $result;
    }
}