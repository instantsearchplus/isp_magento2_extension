<?php

namespace Autocompleteplus\Autosuggest\Model\Plugin\CatalogSearch\Result;

class Index
{
    public function aroundExecute(\Magento\CatalogSearch\Controller\Result\Index $subject, \Closure $proceed)
    {
        // TODO: Implement aroundExecute method.
        $returnValue = $proceed();
        return $returnValue;
    }
}
