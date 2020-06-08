<?php
/**
 * CategoryInit.php
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

class CategoryInit implements ObserverInterface
{
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        if ($this->registry->registry('in_category')) {
            $layoutUpdates = $category->getCustomLayoutUpdate();
            if (!$layoutUpdates) {
                $layoutUpdates = '';
            }
            $layoutUpdates .= '<referenceContainer name="sidebar.main">
                                <referenceBlock name="catalog.leftnav" remove="true" />
                               </referenceContainer>';
            $category->setCustomLayoutUpdate($layoutUpdates);
        }
    }
}