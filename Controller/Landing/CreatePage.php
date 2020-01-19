<?php
/**
 * CreatePage.php
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
 * Class CreatePage
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
class CreatePage extends \Autocompleteplus\Autosuggest\Controller\Landing
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
        $data = json_decode($data_json, true);

        $slug = $request->getParam('slug');
        $title = $request->getParam('title', '');
        $is_serp = $request->getParam('is_serp', '0');

        if (!$slug && (!$data || count($data) == 0)) {
            $response = [
                'success' => false,
                'error' => 'no slug found'
            ];
            $result->setData($response);
            return $result;
        }

        if ($slug) {
            $data[$slug] = $title;
        }
        $response = array();
        foreach ($data as $slug => $title) {
            $response = $this->createSinglePage($title, $response, $slug, $storeId, $is_serp);
        }

        $result->setData($response);
        return $result;
    }

    /**
     * @param $title
     * @param $request
     * @param $slug
     * @param $storeId
     * @param $is_serp
     * @return array
     */
    protected function createSinglePage($title, $response, $slug, $storeId, $is_serp)
    {
        if (strlen($slug) > 50) {
            $response[$slug] = [
                'success' => false,
                'error' => 'slug is not valid'
            ];
            return $response;
        }

        $request = $this->getRequest();
        if ($title == '') {
            $title = ucfirst($request->getParam('slug', $slug));
        }

        try {
            $page = $this->pageFactory->create();
            if ($page->checkIdentifier($slug, $storeId)) {
                $response[$slug] = [
                    'success' => false,
                    'error' => 'The page with this identifier(slug) already exists'
                ];
            } else {
                $page_content = sprintf(
                    '{{widget type="Autocompleteplus\Autosuggest\Block\Widget\LandingPage" is_serp="%s" slug="%s"}}',
                    $is_serp,
                    $slug
                );
                $page->setTitle($title)
                    ->setIdentifier($slug)
                    ->setIsActive(true)
                    ->setPageLayout('1column')
                    ->setStores([$storeId])
                    ->setContent($page_content)
                    ->save();
                $this->clearCache();
                $response[$slug] = [
                    'success' => true
                ];
                if ((int)$is_serp == 1) {
                    $this->helper->setSerpSlug($slug, 'stores', $storeId);
                }
            }
        } catch (\Exception $e) {
            $response[$slug] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
        return $response;
    }
}
