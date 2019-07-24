<?php
/**
 * Checkslug.php
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

namespace Autocompleteplus\Autosuggest\Controller\Landing;

/**
 * Class Checkslug
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
class Checkslug extends \Autocompleteplus\Autosuggest\Controller\Landing
{
    public function execute()
    {
        $request = $this->getRequest();
        $authKey = $request->getParam('authentication_key');
        $uuid = $request->getParam('uuid');
        $storeId = $request->getParam('store_id');
        $result = $this->resultJsonFactory->create();

        if (!$this->isValid($uuid, $authKey)) {
            $response = [
                'success' => false,
                'error' => 'Authentication failed'
            ];
            $result->setData($response);
            return $result;
        }

        $data_json = $request->getParam('data');
        $data = json_decode($data_json);

        $slug = $request->getParam('slug');

        try {
            $page = $this->pageFactory->create();

            if ($page->checkIdentifier($slug, $storeId)) {
                $response = [
                    'success' => false,
                    'error' => 'The page with this identifier(slug) already exists'
                ];
            } else {
                $response = [
                    'success' => true
                ];
            }
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        $result->setData($response);
        return $result;
    }
}
