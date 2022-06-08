<?php
/**
 * Injector File
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

namespace Autocompleteplus\Autosuggest\Block\Html;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Injector
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
class Injector extends \Magento\Framework\View\Element\Template
{
    protected $injectorHelper;
    protected $helper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Autocompleteplus\Autosuggest\Helper\Html\Injector $injectorHelper,
        array $data
    ) {
        $this->helper = $helper;
        $this->injectorHelper = $injectorHelper;
        parent::__construct($context, $data);
    }

    /**
     * GetSrc return script url with params
     *
     * @return string
     */
    public function getSrc()
    {
        return $this->injectorHelper->getSrc();
    }

    public function isEnabled()
    {
        return $this->helper->getEnabled();
    }

    public function isV2Enabled()
    {
        return $this->injectorHelper->isSerpV2V2Enabled();
    }
}
