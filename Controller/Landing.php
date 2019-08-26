<?php
/**
 * Landing.php
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


namespace Autocompleteplus\Autosuggest\Controller;

/**
 * Landing
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
abstract class Landing extends \Magento\Framework\App\Action\Action
{
    protected $helper;
    protected $apiHelper;
    protected $resultJsonFactory;
    protected $pageFactory;
    protected $cacheTypeList;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Api $apiHelper,
        \Autocompleteplus\Autosuggest\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->apiHelper = $apiHelper;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function isValid($uuid, $authKey)
    {
        if ($this->apiHelper->getApiUUID() == $uuid 
            && $this->apiHelper->getApiAuthenticationKey() == $authKey
        ) {
            return true;
        }

        return false;
    }

    public function clearCache()
    {
        $this->cacheTypeList->cleanType('config');
    }
}
