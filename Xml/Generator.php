<?php
/**
 * Generator File
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

namespace Autocompleteplus\Autosuggest\Xml;

/**
 * Generator
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
class Generator implements \Autocompleteplus\Autosuggest\Xml\GeneratorInterface
{
    protected $_xml;

    protected $_rootElementName;

    protected $_rootAttributes;

    public function getRootElementName()
    {
        return $this->_rootElementName;
    }

    public function setRootElementName($name)
    {
        $this->_rootElementName = $name;
        return $this;
    }

    public function setRootAttributes($attributes)
    {
        $this->_rootAttributes = $attributes;
        return $this;
    }

    public function getRootAttributes()
    {
        return $this->_rootAttributes;
    }

    public function getSimpleXml()
    {
        if (!$this->_xml) {
            $rootElement = sprintf('<%s/>', $this->getRootElementName());
            $this->_xml = new \SimpleXMLElement($rootElement);

            $rootAttributes = $this->getRootAttributes();

            if ($rootAttributes) {
                foreach ($rootAttributes as $attrKey => $attrValue) {
                    $this->_xml->addAttribute($attrKey, $attrValue);
                }
            }
        }
        return $this->_xml;
    }

    public function generateXml()
    {
        $domDocument = new \DOMDocument('1.0');
        $domDocument->formatOutput = true;
        $simpleXmlToDom = dom_import_simplexml($this->getSimpleXml());

        $simpleXmlToDom = $domDocument->importNode($simpleXmlToDom, true);
        $domDocument->appendChild($simpleXmlToDom);

        $output = $domDocument->saveXML($domDocument, LIBXML_NOEMPTYTAG);

        return $output;
    }

    public function createChild($childName, $childAttributes, $childValue = false, $parent = false, $stripTags = false)
    {
        $xml = $this->getSimpleXml();

        if ($parent !== false) {
            $xml = $parent;
        }

        $child = $xml->addChild($childName);

        if ($childValue !== false and !is_null($childValue)) {
            if ($child !== null) {
                $node = dom_import_simplexml($child);
                $doc = $node->ownerDocument;

                // Detect if $childValue is already escaped
                $decodedValue = html_entity_decode($childValue);
                if ($decodedValue !== $childValue and $decodedValue) {
                    // $childValue was escaped, so use the decoded version
                    $childValue = $decodedValue;
                }

                if ($stripTags) {
                    // Remove script tags and their content
                    $childValue = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $childValue);

                    // Remove all HTML tags
                    $childValue = strip_tags($childValue);

                    // Decode HTML entities and trim whitespace
                    $childValue = trim(html_entity_decode($childValue));
                }

                $node->appendChild($doc->createCDATASection($childValue));
            }
        }

        if (is_array($childAttributes)) {
            foreach ($childAttributes as $key => $val) {
                $child->addAttribute($key, is_null($val) ? '' : $val);
            }
        }

        return $child;
    }
}
