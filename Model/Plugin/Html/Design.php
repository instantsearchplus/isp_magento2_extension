<?php
/**
 * Design.php
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


class Design
{
    protected $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    public function afterGetDesignSettings($subject, $result) {

        if ($this->registry->registry('in_category')) {
            $layoutUpdatesArr = $result->getData('layout_updates');

            $layoutUpdatesArr[] = '<referenceContainer name="sidebar.main">
                                <referenceBlock name="catalog.leftnav" remove="true" />
                               </referenceContainer>';
            $result->setData('layout_updates', $layoutUpdatesArr);
        }
        return $result;
    }
}