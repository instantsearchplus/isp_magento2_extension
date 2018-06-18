<?php

namespace Autocompleteplus\Autosuggest\Controller\Adminhtml\Redirect;

class Index extends \Autocompleteplus\Autosuggest\Controller\Adminhtml\Redirect
{
    public function execute()
    {
        $dashboardUrl = $this->helper->getDashboardEndpoint();
        $dashboardParams = $this->helper->getDashboardParams();
        return $this->resultRedirectFactory->create()->setUrl($dashboardUrl . '/' . $dashboardParams);
    }
}
