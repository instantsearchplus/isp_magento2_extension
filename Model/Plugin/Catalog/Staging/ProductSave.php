<?php
/**
 * ProductSave.php
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

namespace Autocompleteplus\Autosuggest\Model\Plugin\Catalog\Staging;

/**
 * Class ProductSave
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
class ProductSave
{
    /**
     * @var \Autocompleteplus\Autosuggest\Helper\Batches
     */
    protected $batchesHelper;

    protected $request;

    /**
     * Action constructor.
     * @param \Autocompleteplus\Autosuggest\Helper\Batches $batchesHelper
     */
    public function __construct(
        \Autocompleteplus\Autosuggest\Helper\Batches $batchesHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->batchesHelper = $batchesHelper;
        $this->request = $request;
    }

    public function afterExecute($subject, $result) {
        $product_id = $this->request->getParam('id');
        if ($product_id) {
            $stagingData = $this->request->getParam('staging');
            $startTime = strtotime($stagingData['start_time']);
            $endTime = strtotime($stagingData['end_time']);
            $timeToSchedule = strtotime('now');
            if ($startTime > $timeToSchedule) {
                $timeToSchedule = $startTime;
            } elseif ($endTime > $timeToSchedule) {
                $timeToSchedule = $endTime;
            }
            $this->batchesHelper->writeProductUpdate(null, $product_id, 0, $timeToSchedule, false);
        }
        return $result;
    }
}
