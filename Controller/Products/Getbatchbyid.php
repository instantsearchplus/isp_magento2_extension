<?php
/**
 * Getbatchbyid.php
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

namespace Autocompleteplus\Autosuggest\Controller\Products;

/**
 * Class Getbatchbyid
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
class Getbatchbyid extends \Autocompleteplus\Autosuggest\Controller\Products
{
    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator
     */
    protected $xmlGenerator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    protected $response;

    /**
     * Sendupdated constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Autocompleteplus\Autosuggest\Helper\Product\Xml\Generator $xmlGenerator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->xmlGenerator = $xmlGenerator;
        $this->date = $date;
        $this->response = $context->getResponse();
        parent::__construct($context);
    }

    /**
     * Method execute
     *
     * @return void
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store_id', 1);
        $from = $this->getRequest()->getParam('from');
        $to = $this->getRequest()->getParam('to', false);
        $id = $this->getRequest()->getParam('id', 1);

        $batches_json = $this->xmlGenerator
            ->getSingleBatchTableRecord($id, $storeId);

        $this->response->setHeader('Content-type', 'text/json');
        $this->response->setBody($batches_json);
    }
}
