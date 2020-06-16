<?php
/**
 * IspConfigData.php
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

namespace Autocompleteplus\Autosuggest\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class IspConfigData implements SectionSourceInterface
{
    protected $injectorHelper;
    protected $helper;

    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Html\Injector $injectorHelper
    ) {
        $this->helper = $helper;
        $this->injectorHelper = $injectorHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $output = $this->injectorHelper->getAdditionalParameters();
        return $output;
    }
}