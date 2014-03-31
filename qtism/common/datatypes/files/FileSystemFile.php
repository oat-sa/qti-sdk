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
use \RuntimeException;

/**
 * An implementation of File focusing on storing a file 
 * in a persistent manner, e.g. on the file system.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class FileSystemFile extends AbstractPersistentFile {

    /**
     * The path to the file on the persistent storage.
     * 
     * @var string
     */
    private $path;
    
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
     * @param string $mimeType The MIME type of the file.
     * @param string $path The path where the file is actually stored.
     * @param string $filename The name of the file.
     */
    public function __construct($mimeType, $path, $filename = '') {
        parent::__construct($mimeType, $filename);
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
     * Get the sequence of bytes composing the content of the file.
     * 
     * @throws RuntimeException If the data cannot be retrieved.
     */
    public function getData() {

        $fp = @fopen($this->getPath(), 'r');
        
        if ($fp === false) {
            $msg = "Unable to open QTI file at '" . $this->getPath() . "'.";
            throw new RuntimeException($msg);
        }
        
        // --- Simply consume meta data which is not useful to client code.
        // filename.
        $len = current(unpack('s', fread($fp, 2)));
        $filename = ($len > 0) ? fread($fp, $len) : '';
        
        // MIME type.
        $len = current(unpack('s', fread($fp, 2)));
        $mimeType = fread($fp, $len);
        
        $toRead = filesize($this->getPath()) - 4 - strlen($filename) - strlen($mimeType);
        $data = '';
        
        if ($toRead > 0) {
            $data = fread($fp, $toRead);
        }
        
        @fclose($fp);
        
        return $data;
    }
    
    /**
     * Get a stream resource on the file.
     * 
     * @throws RuntimeException If the stream on the file cannot be open.
     * @return resource An open stream.
     */
    public function getStream() {
        $fp = @fopen($this->getPath(), 'r');
        
        if ($fp === false) {
            $msg = "Cannot retrieve QTI File Stream from '" . $this->getPath() . "'.";
            throw new RuntimeException($msg);
        }
        
        $len = current(unpack('s', fread($fp, 2)));
        fseek($fp, $len, SEEK_CUR);
        
        $len = current(unpack('s', fread($fp, 2)));
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
     * @throws RuntimeException If something wrong happens.
     * @return PersistentFile
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
                $packedFilename = pack('s', $len) . $filename;
                
                // MIME type.
                $len = strlen($mimeType);
                $packedMimeType = pack('s', $len) . $mimeType;
                
                $finalSize = strlen($packedFilename) + strlen($packedMimeType) + filesize($source);
                
                $sourceFp = fopen($source, 'r');
                $destinationFp = fopen($destination, 'a');
                
                fwrite($destinationFp, $packedFilename . $packedMimeType);
                
                // do not eat up memory ;)!
                while (ftell($destinationFp) < $finalSize) {
                    $buffer = fread($sourceFp, self::CHUNK_SIZE);
                    fwrite($destinationFp, $buffer);
                }
                
                @fclose($sourceFp);
                @fclose($destinationFp);
                
                return new static($mimeType, $destination, $filename);
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
     * Retrieve a previously persisted file.
     * 
     * @param string $path The path to the persisted file.
     * @throws RuntimeException If something wrong occurs while retrieving the file.
     * @return PersistentFile
     */
    static public function retrieveFile($path) {
        // Retrieve filename and mime type.
        $fp = @fopen($path, 'r');
        
        if ($fp === false) {
            $msg = "Unable to retrieve QTI file at '${path}.";
            throw new RuntimeException($msg);
        }
        
        // filename.
        $len = current(unpack('s', fread($fp, 2)));
        $filename = ($len > 0) ? fread($fp, $len) : '';
        
        // MIME type.
        $len = current(unpack('s', fread($fp, 2)));
        $mimeType = fread($fp, $len);
        
        @fclose($fp);
        
        return new static($mimeType, $path, $filename);
    }
}