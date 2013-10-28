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

namespace qtism\data\content\xhtml;

use qtism\common\utils\Format;
use qtism\data\content\AtomicInline;
use \InvalidArgumentException;

/**
 * The XHTML img class.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Img extends AtomicInline {
    
    /**
     * The img's src attribute.
     * 
     * @var string
     */
    private $src;
    
    /**
     * The img's src attribute.
     * 
     * @var string
     */
    private $alt;
    
    /**
     * The img's longdesc attribute.
     * 
     * @var string
     */
    private $longdesc = '';
    
    /**
     * The img's height attribute. A negative (< 0) value
     * means that no height is indicated.
     * 
     * @var integer
     */
    private $height = -1;
    
    /**
     * The img's width attribute. A negative (< 0) value
     * means that no width is indicated.
     * 
     * @var integer
     */
    private $width = -1;
    
    /**
     * Create a new Img object.
     * 
     * @param string $src A URI.
     * @param string $alt An alternative text.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The lang of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If one of the argument is invalid.
     */
    public function __construct($src, $alt, $id = '', $class = '', $lang = '', $label = '') {
        parent::__construct($id, $class, $lang, $label);
        $this->setSrc($src);
        $this->setAlt($alt);
        $this->setLongdesc('');
        $this->setHeight(-1);
        $this->setWidth(-1);
    }
    
    /**
     * Set the src attribute.
     * 
     * @param string $src A URI.
     * @throws InvalidArgumentException If $src is not a valid URI.
     */
    public function setSrc($src) {
        if (Format::isUri($src) === true) {
            $this->src = $src;
        }
        else {
            $msg = "The 'src' argument must be a valid URI, '" . $src . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get the src attribute.
     * 
     * @return string A URI.
     */
    public function getSrc() {
        return $this->src;
    }
    
    /**
     * Set the alt attribute.
     * 
     * @param string $alt A non-empty string.
     * @throws InvalidArgumentException If $alt is not a non-empty string.
     */
    public function setAlt($alt) {
        if (is_string($alt) && empty($alt) === false) {
            $this->alt = $alt;
        }
        else {
            $msg = "The 'alt' argument must be a non-empty string, '" . gettype($alt) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get the longdesc attribute.
     * 
     * @param string $longdesc A valid URI.
     * @throws InvalidArgumentException If $longdesc is not a valid URI.
     */
    public function setLongdesc($longdesc) {
        if (Format::isUri($longdesc) === true) {
            $this->longdesc = $longdesc;
        }
        else {
            $msg = "The 'longdesc' argument must be a valid URI, '" . $longdesc . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get the longdesc attribute.
     * 
     * @return string A URI.
     */
    public function getLongdesc() {
        return $this->longdesc;
    }
    
    /**
     * Set the height attribute. A negative (< 0) value for $height means there
     * is no height indicated.
     * 
     * @param integer $height An integer.
     * @throws InvalidArgumentException If $height is not a valid integer value.
     */
    public function setHeight($height) {
        if (is_int($height) === true) {
            $this->height = $height;
        }
        else {
            $msg = "The 'height' argument must be an integer, '" . gettype($height) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get the height attribute. A negative (< 0) value for $height means there
     * is no height indicated.
     * 
     * @return integer A height.
     */
    public function getHeight() {
        return $this->height;
    }
    
    /**
     * Set the width attribute. A negative (< 0) value for $width means there
     * is no width indicated.
     * 
     * @param integer $width A width.
     * @throws InvalidArgumentException If $width is not an integer value.
     */
    public function setWidth($width) {
        if (is_int($width) === true) {
            $this->width = $width;
        }
        else {
            $msg = "The 'width' argument must be an integer, '" . gettype($width) . "'.";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get the width attribute. A negative (< 0) value for $width means there
     * is no width indicated.
     * 
     * @return integer a width.
     */
    public function getWidth() {
        return $this->width;
    }
    
    public function getQtiClassName() {
        return 'img';
    }
}