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
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\datatypes\files;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\datatypes\File;
use \RuntimeException;

/**
 * An implementation of File focusing on storing a file 
 * in a persistent manner, e.g. on the file system.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class FileSystemFile implements File {

    /**
     * The path to the file on the persistent storage.
     * 
     * @var string
     */
    private $path;
    
    /**
     * The MIME type of the file content.
     * 
     * @var string
     */
    private $mimeType;
    
    /**
     * The optional file name.
     * 
     * @var string
     */
    private $filename = '';
    
    /**
     * When dealing with files, compare, read, write, ...
     * in CHUNK_SIZE to not eat up memory.
     * 
     * @var integer
     */
    const CHUNK_SIZE = 2048;
    
    /**
     * Create a new PersistentFile object.
     * 
     * @param string $path The path where the file is actually stored.
     * @throws \RuntimeException If the file cannot be retrieved correctly.
     */
    public function __construct($path) {
        // Retrieve filename and mime type.
        $fp = @fopen($path, 'r');
        
        if ($fp === false) {
            $msg = "Unable to retrieve QTI file at '${path}.";
            throw new RuntimeException($msg);
        }
        
        // filename.
        $len = current(unpack('S', fread($fp, 2)));
        $filename = ($len > 0) ? fread($fp, $len) : '';
        
        // MIME type.
        $len = current(unpack('S', fread($fp, 2)));
        $mimeType = fread($fp, $len);
        
        @fclose($fp);
        
        $this->setFilename($filename);
        $this->setMimeType($mimeType);
        $this->setPath($path);
    }
    
    /**
     * Set the path to the file.
     * 
     * @param string $path
     */
    protected function setPath($path) {
        $this->path = $path;
    }
    
    /**
     * Get the path to the file.
     * 
     * @return string
     */
    public function getPath() {
        return $this->path;
    }
    
    /**
     * Set the mime-type of the file.
     * 
     * @param string $mimeType
     */
    protected function setMimeType($mimeType) {
        $this->mimeType = $mimeType;
    }
    
    /**
     * Get the mime-type of the file.
     * 
     * @return string
     */
    public function getMimeType() {
        return $this->mimeType;
    }
    
    /**
     * Get the name of the file.
     * 
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }
    
    /**
     * Set the name of the file.
     * 
     * @param string $filename
     */
    protected function setFilename($filename) {
        $this->filename = $filename;
    }
    
    /**
     * Get the sequence of bytes composing the content of the file.
     * 
     * @throws \RuntimeException If the data cannot be retrieved.
     */
    public function getData() {

        $fp = $this->getStream();
        $data = '';
        
        while (feof($fp) === false) {
            $data .= fread($fp, self::CHUNK_SIZE);
        }
        
        @fclose($fp);
        
        return $data;
    }
    
    /**
     * Get a stream resource on the file.
     * 
     * @throws \RuntimeException If the stream on the file cannot be open.
     * @return resource An open stream.
     */
    public function getStream() {
        $fp = @fopen($this->getPath(), 'rb');
        
        if ($fp === false) {
            $msg = "Cannot retrieve QTI File Stream from '" . $this->getPath() . "'.";
            throw new RuntimeException($msg);
        }
        
        $len = current(unpack('S', fread($fp, 2)));
        fseek($fp, $len, SEEK_CUR);
        
        $len = current(unpack('S', fread($fp, 2)));
        fseek($fp, $len, SEEK_CUR);
        
        return $fp;
    }
    
    /**
     * Create a PersistentFile object from an existing file.
     * 
     * @param string $source The source path.
     * @param string $destination Where the file resulting from $source will be stored.
     * @param string $mimeType The MIME type of the file.
     * @param mixed $withFilename Whether or not consider the $source's filename to be the $destination's file name. Give true to use the current file name. Give a string to select a different one. Default is true.
     * @throws \RuntimeException If something wrong happens.
     * @return \qtism\common\datatypes\files\FileSystemFile
     */
    static public function createFromExistingFile($source, $destination, $mimeType, $withFilename = true) {
        
        if (is_file($source) === true) {
            
            if (is_readable($source) === true) {
                
                // Should we build the path to $destination?
                $pathinfo = pathinfo($destination);
                if (isset($pathinfo['dirname']) === false) {
                    $msg = "The destination argument '${destination}' is a malformed path.";
                    throw new RuntimeException($msg);
                }
                
                if (is_dir($pathinfo['dirname']) === false) {
                    
                    if (($mkdir = @mkdir($pathinfo['dirname'], '0770', true)) === false) {
                        $msg = "Unable to create destination directory at '" . $pathinfo['dirname'] . "'.";
                        throw new RuntimeException($msg);
                    }
                }
                    
                $filename = '';
                $pathinfo = pathinfo($source);
                $filename = ($withFilename === true) ? ($pathinfo['filename'] . '.' . $pathinfo['extension']) : strval($withFilename);
                
                // --- We store the file name and the mimetype in the file itself.
                
                // filename.
                $len = strlen($filename);
                $packedFilename = pack('S', $len) . $filename;
                
                // MIME type.
                $len = strlen($mimeType);
                $packedMimeType = pack('S', $len) . $mimeType;
                
                $finalSize = strlen($packedFilename) + strlen($packedMimeType) + filesize($source);
                
                $sourceFp = fopen($source, 'r');
                $destinationFp = fopen($destination, 'w');
                
                fwrite($destinationFp, $packedFilename . $packedMimeType);
                
                // do not eat up memory ;)!
                while (ftell($destinationFp) < $finalSize) {
                    $buffer = fread($sourceFp, self::CHUNK_SIZE);
                    fwrite($destinationFp, $buffer);
                }
                
                @fclose($sourceFp);
                @fclose($destinationFp);
                
                return new static($destination);
            }
            else {
                // Source file not readable.
                $msg = "File '${source}' found but not readable.";
                throw new RuntimeException($msg);
            }
        }
        else {
            // Source file not found.
            $msg = "Unable to find source file at '${source}'.";
            throw new RuntimeException($msg);
        }
    }
    
    /**
     * Create a File from existing data.
     * 
     * @param string $data
     * @param string $destination
     * @param string $mimeType
     * @param string $filename
     * @return \qtism\common\datatypes\files\FileSystemFile
     */
    static public function createFromData($data, $destination, $mimeType, $filename = '') {
        $tmp = tempnam('/tmp', 'qtism');
        file_put_contents($tmp, $data);
        
        $file = self::createFromExistingFile($tmp, $destination, $mimeType, $filename);
        unlink($tmp);
        return $file;
    }
    
    /**
     * Retrieve a previously persisted file.
     * 
     * @param string $path The path to the persisted file.
     * @throws \RuntimeException If something wrong occurs while retrieving the file.
     * @return \qtism\common\datatypes\files\FileSystemFile
     */
    static public function retrieveFile($path) {
        return new static($path);
    }
    
    /**
     * Get the cardinality of the File value.
     * 
     * @return integer A value from the Cardinality enumeration.
     */
    public function getCardinality() {
        return Cardinality::SINGLE;
    }
    
    /**
     * Get the baseType of the File value.
     * 
     * @return integer A value from the BaseType enumeration.
     */
    public function getBaseType() {
        return BaseType::FILE;
    }
    
    /**
     * Whether or not the File has a file name.
     * 
     * @return boolean
     */
    public function hasFilename() {
        return $this->getFilename() !== '';
    }
    
    /**
     * Whether or not two File objects are equals. Two File values
     * are considered to be identical if they have the same file name,
     * mime-type and data.
     * 
     * @return boolean
     */
    public function equals($obj) {
        if ($obj instanceof File) {
            if ($this->getFilename() !== $obj->getFilename()) {
                return false;
            }
            else if ($this->getMimeType() !== $obj->getMimeType()) {
                return false;
            }
            else {
                // We have to check the content of the file.
                $myStream = $this->getStream();
                $objStream = $obj->getStream();
                
                while (feof($myStream) === false && feof($objStream) === false) {
                    $myChunk = fread($myStream, self::CHUNK_SIZE);
                    $objChjunk = fread($objStream, self::CHUNK_SIZE);
                    
                    if ($myChunk !== $objChjunk) {
                        @fclose($myStream);
                        @fclose($objStream);
                        
                        return false;
                    }
                }
                
                @fclose($myStream);
                @fclose($objStream);
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get the unique identifier of the File.
     * 
     * @return string
     */
    public function getIdentifier() {
        return $this->getPath();
    }
    
    public function __toString() {
        return $this->getFilename();
    }
}