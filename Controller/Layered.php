<?php
/**
 * Layered File
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

namespace Autocompleteplus\Autosuggest\Controller;

/**
 * Layered
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
abstract class Layered extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $apiHelper;
    protected $resultJsonFactory;
    protected $cacheTypeList;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        $this->apiHelper = $apiHelper;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context);
    }

    public function isValid($uuid, $authKey)
    {
        if ($this->apiHelper->getApiUUID() == $uuid &&
            $this->apiHelper->getApiAuthenticationKey() == $authKey) {
            return true;
        }

        return false;
    }

    public function clearCache()
    {
        $this->cacheTypeList->cleanType('config');
    }
}
