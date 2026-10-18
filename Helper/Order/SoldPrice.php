<?php
/**
 * SoldPrice File
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package   Instantsearchplus
 * @author    Fast Simon <info@instantsearchplus.com>
 * @copyright 2014 Fast Simon (http://www.instantsearchplus.com)
 * @license   Open Software License (OSL 3.0)*
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

namespace Autocompleteplus\Autosuggest\Helper\Order;

/**
 * The unit price a shopper paid for an order line, net of discounts, in base currency.
 */
class SoldPrice
{
    /**
     * base_row_total is excl tax (CommonTaxCollector::updateItemTaxInfo). The discount compensation
     * removes the tax share of a discount taken from a tax-inclusive price, and is 0 otherwise. When
     * catalog prices include tax, the line's tax is added back, so the result keeps the basis the
     * catalog price had.
     *
     * A dynamic-price bundle parent carries no discount or tax of its own (RulesApplier resets it):
     * those terms are summed over its children.
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem the priced row: a configurable's parent row
     * @param bool $priceIncludesTax the order store's "Catalog Prices: Including Tax"
     * @return float
     */
    public static function unitPrice($orderItem, bool $priceIncludesTax): float
    {
        $qty = (float)$orderItem->getQtyOrdered();
        if ($qty <= 0) {
            return round((float)$orderItem->getBasePrice(), 4);
        }

        $termItems = [$orderItem];
        if ($orderItem->isChildrenCalculated() && !$orderItem->getParentItem()) {
            $termItems = $orderItem->getChildrenItems();
        }

        $net = (float)$orderItem->getBaseRowTotal();
        foreach ($termItems as $termItem) {
            $net -= (float)$termItem->getBaseDiscountAmount()
                - (float)$termItem->getBaseDiscountTaxCompensationAmount();
            if ($priceIncludesTax) {
                $net += (float)$termItem->getBaseTaxAmount();
            }
        }

        // Four decimals, not two: hermes multiplies by quantity, and a cent-rounded unit price can
        // erase a discount spread over many units (0.40 off 100 units at 1.00 would read 100.00).
        return round($net / $qty, 4);
    }

    /**
     * True for a child row whose sale the parent row already reports: a configurable's simple child,
     * or a dynamic-price bundle's selection (the parent row carries the children's total). Only when
     * the parent's product still loads; otherwise the parent row is not emitted and the child is the
     * sole row capturing the purchase. Fixed-price bundle and grouped children keep their own rows.
     *
     * @param \Magento\Sales\Model\Order\Item|\Magento\Quote\Model\Quote\Item $item
     * @return bool
     */
    public static function isReportedByParent($item): bool
    {
        $parentItem = $item->getParentItem();
        if (!is_object($parentItem) || !is_object($parentItem->getProduct())) {
            return false;
        }
        return $parentItem->getProductType() == 'configurable'
            || ($parentItem->getProductType() == 'bundle' && $parentItem->isChildrenCalculated());
    }

    /**
     * Core prices a configurable line on its parent row only (Subtotal::collect), so a child emitted
     * because its parent's product is gone carries a 0 row total: price it from that parent row.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return \Magento\Sales\Model\Order\Item
     */
    public static function pricedRow($item)
    {
        $parentItem = $item->getParentItem();
        if (is_object($parentItem) && $parentItem->getProductType() == 'configurable') {
            return $parentItem;
        }
        return $item;
    }
}
