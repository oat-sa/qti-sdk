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

use qtism\common\datatypes\File;

/**
 * An implementation of File focusing on storing a file exclusively
 * in memory.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class MemoryFile extends AbstractFile {
    
    /**
     * The sequence of bytes composing the file,
     * as a binary string.
     * 
     * @var string
     */
    private $data;
    
    /**
     * Create a new instance of MemoryFile.
     * 
     * @param string $data The sequence of bytes composing the file content.
     * @param string $mimeType The MIME type of the file.
     * @param string $filename An optional file name.
     */
    public function __construct($data, $mimeType, $filename = '') {
        $this->setData($data);
        $this->setMimeType($mimeType);
        $this->setFilename($filename);
    }
    
    /**
     * Set the sequence of bytes composing the file.
     * 
     * @param string $data A binary string.
     */
    protected function setData($data) {
        $this->data = $data;
    }
    
    /**
     * Get the sequence of bytes composing the file.
     * 
     * @return string A binary string.
     */
    public function getData() {
        return $this->data;
    }
}