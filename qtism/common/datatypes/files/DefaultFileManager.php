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

use \RuntimeException;

/**
 * This implementation of FileManager is the default one of QTISM. 
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class DefaultFileManager implements FileManager {
    
    private $storageDirectory;
    
    public function __construct($storageDirectory) {
        $this->setStorageDirectory($storageDirectory);
    }
    
    protected function setStorageDirectory($storageDirectory) {
        $this->storageDirectory = $storageDirectory;
    }
    
    protected function getStorageDirectory() {
        return $this->storageDirectory;
    }
    
    /**
     * Create a file handled exclusively in memory.
     * 
     * @param string $data The sequence of bytes composing the file content.
     * @param string $mimeType The MIME type of the file.
     * @param string $filename An optional file name.
     */
    public function createMemoryFile($data, $mimeType, $filename = '') {
        return new MemoryFile($data, $mimeType, $filename);
    }
    
    /**
     * 
     * 
     * @param string $path
     * @param string $mimeType
     * @param string $filename
     * @throws FileManagerException
     * @return FileSystemFile
     */
    public function createPersistentFile($path, $mimeType, $filename = '') {
        $destination = rtrim($this->getStorageDirectory(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . uniqid('qtism', true);
        
        try {
            return FileSystemFile::createFromExistingFile($path, $destination, $mimeType, $filename);
        }
        catch (RuntimeException $e) {
            $msg = "An error occured while creating a QTI FileSystemFile object.";
            throw new FileManagerException($msg, 0, $e);
        }
    }
    
    /**
     * 
     * 
     * @throws FileManagerException
     */
    public function deletePersistentFile(AbstractPersistentFile $file) {
        
        $deletion = @unlink($file->getPath());
        if ($deletion === false) {
            $msg = "The File System File located at '" . $file->getPath() . "' could not be deleted gracefully.";
            throw new FileManagerException($msg);
        }
    }
}