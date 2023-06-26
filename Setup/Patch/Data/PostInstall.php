<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Autocompleteplus\Autosuggest\Setup\Patch\Data;

use Autocompleteplus\Autosuggest\Helper\Api;
use Autocompleteplus\Autosuggest\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PostInstall implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Api
     */
    protected $api;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ScopeConfigInterface $scopeConfig
     * @param Data $helper
     * @param Api $api
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ScopeConfigInterface     $scopeConfig,
        Data                     $helper,
        Api                      $api,
        LoggerInterface          $logger
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->api = $api;
        $this->logger = $logger;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $params = [
            'site' => $this->scopeConfig->getValue(
                'web/unsecure/base_url',
                ScopeInterface::SCOPE_STORE
            ),
            'email' => $this->scopeConfig->getValue(
                'trans_email/ident_support/email',
                ScopeInterface::SCOPE_STORE
            ),
            'f' => $this->helper->getVersion(),
            'multistore' => json_encode($this->helper->getMultiStoreData())
        ];

        $uuid = $this->api->getApiUUID();

        if (!empty($uuid)) {
            $params['uuid'] = $uuid;
        }

        $this->api->setUrl($this->api->getApiEndpoint() . '/install');
        $this->api->setRequestType(\Laminas\Http\Request::METHOD_POST);

        try {
            $response = $this->api->buildRequest($params);

            $responseData = json_decode($response->getBody());
            if ($responseData) {
                $this->api->setApiUUID($responseData->uuid);
                $this->api->setApiAuthenticationKey($responseData->authentication_key);
            }

        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }
}
