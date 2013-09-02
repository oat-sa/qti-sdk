<?php

namespace qtism\runtime\storage\binary;

use qtism\runtime\storage\common\IStream;

/**
 * The BinaryStreamAccess aims at providing the needed methods to
 * easily read the data inside BinaryStream objects.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BinaryStreamAccess {
    
    /**
     * The IStream object to read.
     *
     * @var IStream.
     */
    private $stream;
    
    /**
     * Create a new BinaryStreamAccess object.
     *
     * @param IStream $stream An IStream object to be read.
     * @throws StreamReaderException If $stream is not open yet.
     */
    public function __construct(IStream $stream) {
        $this->setStream($stream);
    }
    
    /**
     * Get the IStream object to be read.
     *
     * @return IStream An IStream object.
     */
    protected function getStream() {
        return $this->stream;
    }
    
    /**
     * Set the IStream object to be read.
     *
     * @param IStream $stream An IStream object.
     * @throws StreamReaderException If the $stream is not open yet.
     */
    protected function setStream(IStream $stream) {
    
        if ($stream->isOpen() === false) {
            $msg = "A BinaryStreamAccess do not accept closed streams to be read.";
            throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::NOT_OPEN);
        }
    
        $this->stream = $stream;
    }
    
    /**
     * Read a single byte unsigned integer from the current binary stream.
     * 
     * @throws BinaryStreamAccessException
     * @return integer
     */
    public function readTinyInt() {
        try {
            $bin = $this->getStream()->read(1);
            return ord($bin);
        }
        catch (BinaryStreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::TINYINT);
        }
    }
    
    /**
     * Read a 2 bytes unsigned integer from the current binary stream.
     * 
     * @throws BinaryStreamAccessException
     * @return integer
     */
    public function readShort() {
        try {
            $bin = $this->getStream()->read(2);
            return current(unpack('S', $bin));
        }
        catch (BinaryStreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::SHORT);
        }
    }
    
    /**
     * Read a 8 bytes signed integer from the current binary stream.
     * 
     * @throws BinaryStreamAccessException
     * @return integer
     */
    public function readInt() {
        try {
            $bin = $this->getStream()->read(4);
            return current(unpack('l', $bin));
        }
        catch (BinaryStreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::INT);
        }
    }
    
    /**
     * Read a double precision float from the current binary stream.
     * 
     * @throws BinaryStreamAccessException
     * @return integer
     */
    public function readFloat() {
        try {
            $bin = $this->getStream()->read(8);
            return current(unpack('d', $bin));
        }
        catch (BinaryStreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::FLOAT);
        }
    }
    
    /**
     * Read a boolean value from the current binary stream.
     * 
     * @throws BinaryStreamAccessException
     * @return boolean
     */
    public function readBool() {
        try {
            return ($this->readTinyInt() === 0) ? false : true;
        }
        catch (BinaryStreamAccessException $e) {
            switch ($e->getCode()) {
                case BinaryStreamAccessException::NOT_OPEN:
                    $msg = "Cannot read a boolean float from a closed binary stream.";
                    throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::NOT_OPEN, $e);
                    break;
            
                case BinaryStreamAccessException::TINYINT:
                    $msg = "An error occured while reading a boolean.";
                    throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::BOOLEAN, $e);
                    break;
            
                default:
                    $msg = "An unknown error occured while reading a double precision float.";
                    throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::UNKNOWN, $e);
                break;
            }
        }
    }
    
    /**
     * Read a string value from the current binary stream.
     * 
     * @throws BinaryStreamAccessException
     * @return string
     */
    public function readString() {
        try {
            $length = $this->readShort();
            return $this->getStream()->read($length);
        }
        catch (BinaryStreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::STRING);
        }
        catch (BinaryStreamAccessException $e) {
            switch ($e->getCode()) {
                case BinaryStreamAccessException::NOT_OPEN:
                    $msg = "Cannot read the length of a string from a closed binary stream.";
                    throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::NOT_OPEN, $e);
                    break;
                
                case BinaryStreamAccessException::SHORT:
                    $msg = "An error occured while reading the length of a string.";
                    throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::STRING, $e);
                    break;
                
                default:
                    $msg = "An unknown error occured while reading the length of a string.";
                    throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::UNKNOWN, $e);
                break;
            }
        }
    }
    
    /**
     * Read binary data from the current binary stream.
     * 
     * @throws BinaryStreamAccessException
     * @return string A binary string.
     */
    public function readBinary() {
        try {
            return $this->readString();
        }
        catch (BinaryStreamAccessException $e) {
            switch ($e->getCode()) {
                case BinaryStreamAccessException::NOT_OPEN:
                    $msg = "Cannot read binary data from a closed binary stream.";
                    throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::NOT_OPEN, $e);
                    break;
                
                case BinaryStreamAccessException::SHORT:
                    $msg = "An error occured while reading binary data.";
                    throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::BINARY, $e);
                    break;
                
                default:
                    $msg = "An unknown error occured while reading binary data.";
                    throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::UNKNOWN, $e);
                break;
            }
        }
    }
    
    /**
     * Handle a BinaryStreamException in order to throw the relevant BinaryStreamAccessException.
     * 
     * @param BinaryStreamException $e The BinaryStreamException object to deal with.
     * @param unknown_type $typeError The BinaryStreamAccess exception code to be trown in case of READ error.
     * @throws BinaryStreamAccessException The resulting BinaryStreamAccessException.
     */
    protected function handleBinaryStreamException(BinaryStreamException $e, $typeError) {
        
        $strType = 'unknown datatype';
        
        switch ($typeError) {
            case BinaryStreamAccessException::BOOLEAN:
                $strType = 'boolean';
            break;
            
            case BinaryStreamAccessException::BINARY:
                $strType = 'binary data';
            break;
            
            case BinaryStreamAccessException::FLOAT:
                $strType = 'double precision float';
            break;
            
            case BinaryStreamAccessException::INT:
                $strType = 'integer';
            break;
            
            case BinaryStreamAccessException::SHORT:
                $strType = 'short integer';
            break;
            
            case BinaryStreamAccessException::STRING:
                $strType = 'string';
            break;
            
            case BinaryStreamAccessException::TINYINT:
                $strType = 'tiny integer';
            break;
        }
        
        switch ($e->getCode()) {
            case BinaryStreamException::NOT_OPEN:
                $msg = "Cannot read ${strType} from a closed binary stream.";
                throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::NOT_OPEN, $e);
                break;
        
            case BinaryStreamException::READ:
                $msg = "An error occured while reading a ${strType}.";
                throw new BinaryStreamAccessException($msg, $this, $typeError, $e);
                break;
        
            default:
                $msg = "An unknown error occured while reading a ${strType}.";
                throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::UNKNOWN, $e);
                break;
        }
    }
}