<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package
 */

namespace qtism\data\content;

use qtism\data\QtiComponentCollection;

use qtism\data\QtiComponent;

/**
 * From IMS QTI:
 * 
 * A text run is simply a run of characters. Unlike all other elements in the 
 * content model it is not a sub-class of bodyElement. To assign attributes to a 
 * run of text you must use the span element instead.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TextRun extends QtiComponent implements FlowStatic, InlineStatic, TextOrVariable {
    
    /**
     * The base URI.
     *
     * @var string
     * @qtism-bean-property
     */
    private $base = '';
    
    /**
     * The characters contained in the TextRun.
     * 
     * @var string
     * @qtism-bean-property
     */
    private $content;
    
    /**
     * Create a new TextRun object.
     * 
     * @param string $content The characters to be contained by the TextRun.
     */
    public function __construct($content) {
        $this->setContent($content);
    }
    
    /**
     * Set the characters to be contained by the TextRun.
     * 
     * @param string $content A string value.
     */
    public function setContent($content) {
        $this->content = $content;
    }
    
    /**
     * Get the characters contained by the TextRun.
     * 
     * @return string A string value.
     */
    public function getContent() {
        return $this->content;
    }
    
    /**
     * Set the base URI.
     *
     * @param string $base A URI.
     * @throws InvalidArgumentException if $base is not a valid URI nor an empty string.
     */
    public function setBase($base = '') {
        if (is_string($base) && (empty($base) || Format::isUri($base))) {
            $this->base = $base;
        }
        else {
            $msg = "The 'base' argument must be an empty string or a valid URI, '" . $base . "' given";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get the base URI.
     *
     * @return string An empty string or a URI.
     */
    public function getBase() {
        return $this->base;
    }
    
    /**
     * 
     * @return QtiComponentCollection
     */
    public function getComponents() {
        return new QtiComponentCollection();
    }
    
    public function getQtiClassName() {
        return 'textRun';
    }
}