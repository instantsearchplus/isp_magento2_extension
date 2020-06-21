<?php
/**
 * CategoryView.php
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

namespace Autocompleteplus\Autosuggest\Model\Plugin\Html;


class CategoryView
{
    protected $helper;
    protected $registry;
    protected $storeManager;

    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
    }

    public function aroundExecute($subject,
        \Closure $proceed
    ) {
        if ($this->helper->getSmartNavigationNative($this->storeManager->getStore()->getId())) {
            $this->registry->register('in_category', true);
        }
        $this->registry->register('category_id', $subject->getRequest()->getParam('id'));
        $page = $proceed();
        $page->getConfig()->addBodyClass('category-id-' . $subject->getRequest()->getParam('id'));
        return $page;
    }
}