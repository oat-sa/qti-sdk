<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * @subpackage
 *
 */

namespace qtism\common\datatypes\files;

use qtism\common\Comparable;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\datatypes\File;

/**
 * A default File for QTISM. It implements File partially.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class AbstractFile implements File, Comparable {
    
    /**
     * The MIME type describing the content of the file.
     *
     * @var string
     */
    private $mimeType;
    
    /**
     * An optional file name. An empty string
     * represents no file name.
     *
     * @var string
     */
    private $filename;
    
    public function __construct($mimeType, $filename = '') {
        $this->setMimeType($mimeType);
        $this->setFilename($filename);
    }
    
    public abstract function getData();
    
    /**
     * Set the MIME type describing the content of the file.
     * 
     * @param string $mimeType
     */
    protected function setMimeType($mimeType) {
        $this->mimeType = $mimeType;
    }
    
    /**
     * Get the MIME type describing the content of the file.
     * 
     * @return string
     */
    public function getMimeType() {
        return $this->mimeType;
    }
    
    /**
     * Set the optional name of the file.
     * 
     * @param string $filename
     */
    protected function setFilename($filename) {
        $this->filename = $filename;
    }
    
    /**
     * Get the optional name of the file.
     * 
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }
    
    /**
     * Whether or not this file has a filename.
     * 
     * @return boolean
     */
    public function hasFilename() {
        return $this->getFilename() !== '';
    }
    
    public function getCardinality() {
        return Cardinality::SINGLE;
    }
    
    public function getBaseType() {
        return BaseType::FILE;
    }
    
    public function equals($obj) {
        if ($obj instanceof File) {
            return $this->getData() === $obj->getData() && $this->getFilename() === $obj->getFilename() && $this->getMimeType() === $obj->getMimeType();
        }
    
        return false;
    }
}