<?php

namespace qtism\runtime\storage\binary;

use qtism\runtime\storage\common\AbstractStreamReader;

/**
 * The BinaryStreamReader aims at providing the needed methods to
 * easily read the data inside BinaryStream objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BinaryStreamReader extends AbstractStreamReader {
    
    /**
     * Read a single byte unsigned integer from the current binary stream.
     * 
     * @throws BinaryStreamReaderException
     * @return integer
     */
    public function readTinyInt() {
        try {
            $bin = $this->getStream()->read(1);
            return ord($bin);
        }
        catch (BinaryStreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamReaderException::TINYINT);
        }
    }
    
    /**
     * Read a 2 bytes unsigned integer from the current binary stream.
     * 
     * @throws BinaryStreamReaderException
     * @return integer
     */
    public function readShort() {
        try {
            $bin = $this->getStream()->read(2);
            return current(unpack('S', $bin));
        }
        catch (BinaryStreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamReaderException::SHORT);
        }
    }
    
    /**
     * Read a 8 bytes signed integer from the current binary stream.
     * 
     * @throws BinaryStreamReaderException
     * @return integer
     */
    public function readInt() {
        try {
            $bin = $this->getStream()->read(4);
            return current(unpack('l', $bin));
        }
        catch (BinaryStreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamReaderException::INT);
        }
    }
    
    /**
     * Read a double precision float from the current binary stream.
     * 
     * @throws BinaryStreamReaderException
     * @return integer
     */
    public function readFloat() {
        try {
            $bin = $this->getStream()->read(8);
            return current(unpack('d', $bin));
        }
        catch (BinaryStreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamReaderException::FLOAT);
        }
    }
    
    /**
     * Read a boolean value from the current binary stream.
     * 
     * @throws BinaryStreamReaderException
     * @return boolean
     */
    public function readBool() {
        try {
            return ($this->readTinyInt() === 0) ? false : true;
        }
        catch (BinaryStreamReaderException $e) {
            switch ($e->getCode()) {
                case BinaryStreamReaderException::NOT_OPEN:
                    $msg = "Cannot read a boolean float from a closed binary stream.";
                    throw new BinaryStreamReaderException($msg, $this, BinaryStreamReaderException::NOT_OPEN, $e);
                    break;
            
                case BinaryStreamReaderException::TINYINT:
                    $msg = "An error occured while reading a boolean.";
                    throw new BinaryStreamReaderException($msg, $this, BinaryStreamReaderException::BOOLEAN, $e);
                    break;
            
                default:
                    $msg = "An unknown error occured while reading a double precision float.";
                    throw new BinaryStreamReaderException($msg, $this, BinaryStreamReaderException::UNKNOWN, $e);
                break;
            }
        }
    }
    
    /**
     * Read a string value from the current binary stream.
     * 
     * @throws BinaryStreamReaderException
     * @return string
     */
    public function readString() {
        try {
            $length = $this->readShort();
            return $this->getStream()->read($length);
        }
        catch (BinaryStreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamReaderException::STRING);
        }
        catch (BinaryStreamReaderException $e) {
            switch ($e->getCode()) {
                case BinaryStreamReaderException::NOT_OPEN:
                    $msg = "Cannot read the length of a string from a closed binary stream.";
                    throw new BinaryStreamReaderException($msg, $this, BinaryStreamReaderException::NOT_OPEN, $e);
                    break;
                
                case BinaryStreamReaderException::SHORT:
                    $msg = "An error occured while reading the length of a string.";
                    throw new BinaryStreamReaderException($msg, $this, BinaryStreamReaderException::STRING, $e);
                    break;
                
                default:
                    $msg = "An unknown error occured while reading the length of a string.";
                    throw new BinaryStreamReaderException($msg, $this, BinaryStreamReaderException::UNKNOWN, $e);
                break;
            }
        }
    }
    
    /**
     * Read binary data from the current binary stream.
     * 
     * @throws BinaryStreamReaderException
     * @return string A binary string.
     */
    public function readBinary() {
        try {
            return $this->readString();
        }
        catch (BinaryStreamReaderException $e) {
            switch ($e->getCode()) {
                case BinaryStreamReaderException::NOT_OPEN:
                    $msg = "Cannot read binary data from a closed binary stream.";
                    throw new BinaryStreamReaderException($msg, $this, BinaryStreamReaderException::NOT_OPEN, $e);
                    break;
                
                case BinaryStreamReaderException::SHORT:
                    $msg = "An error occured while reading binary data.";
                    throw new BinaryStreamReaderException($msg, $this, BinaryStreamReaderException::BINARY, $e);
                    break;
                
                default:
                    $msg = "An unknown error occured while reading binary data.";
                    throw new BinaryStreamReaderException($msg, $this, BinaryStreamReaderException::UNKNOWN, $e);
                break;
            }
        }
    }
    
    /**
     * Handle a BinaryStreamException in order to throw the relevant BinaryStreamReaderException.
     * 
     * @param BinaryStreamException $e The BinaryStreamException object to deal with.
     * @param unknown_type $typeError The BinaryStreamReader exception code to be trown in case of READ error.
     * @throws BinaryStreamReaderException The resulting BinaryStreamReaderException.
     */
    protected function handleBinaryStreamException(BinaryStreamException $e, $typeError) {
        
        $strType = 'unknown datatype';
        
        switch ($typeError) {
            case BinaryStreamReaderException::BOOLEAN:
                $strType = 'boolean';
            break;
            
            case BinaryStreamReaderException::BINARY:
                $strType = 'binary data';
            break;
            
            case BinaryStreamReaderException::FLOAT:
                $strType = 'double precision float';
            break;
            
            case BinaryStreamReaderException::INT:
                $strType = 'integer';
            break;
            
            case BinaryStreamReaderException::SHORT:
                $strType = 'short integer';
            break;
            
            case BinaryStreamReaderException::STRING:
                $strType = 'string';
            break;
            
            case BinaryStreamReaderException::TINYINT:
                $strType = 'tiny integer';
            break;
        }
        
        switch ($e->getCode()) {
            case BinaryStreamException::NOT_OPEN:
                $msg = "Cannot read ${strType} from a closed binary stream.";
                throw new BinaryStreamReaderException($msg, $this, BinaryStreamReaderException::NOT_OPEN, $e);
                break;
        
            case BinaryStreamException::READ:
                $msg = "An error occured while reading a ${strType}.";
                throw new BinaryStreamReaderException($msg, $this, $typeError, $e);
                break;
        
            default:
                $msg = "An unknown error occured while reading a ${strType}.";
                throw new BinaryStreamReaderException($msg, $this, BinaryStreamReaderException::UNKNOWN, $e);
                break;
        }
    }
}