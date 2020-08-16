<?php
/**
 * ItemWithScore.php
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

namespace Autocompleteplus\Autosuggest\Model\Plugin\CatalogSearch;


class ItemWithScore
{
    protected $_id;
    protected $_score;

    public function __construct($id, $score)
    {
        $this->_id = $id;
        $this->_score = $score;
    }

    public function getId() {
        return $this->_id;
    }

    public function getScore() {
        return $this->_score;
    }

    public function getCustomAttribute() {
        return $this;
    }

    public function getValue() {
        return $this->_score;
    }
}