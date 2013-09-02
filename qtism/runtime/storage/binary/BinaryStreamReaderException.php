<?php

namespace qtism\runtime\storage\binary;

use \Exception;

/**
 * The BinaryStreamReaderException class represents the error
 * that could occur while reading/extracting data from a BinaryStream
 * object.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BinaryStreamReaderException extends Exception {
    
    /**
     * Unknown error.
     *
     * @var integer
     */
    const UNKNOWN = 0;
    
    /**
     * A closed BinaryStream object is given as the stream to be read.
     *
     * @var integer
     */
    const NOT_OPEN = 1;
    
    /**
     * The AbstractStreamReader that caused the error.
     *
     * @var AbstractStreamReader
     */
    private $source;
    
    /**
     * An error occured while reading a tinyint.
     * 
     * @var integer
     */
    const TINYINT = 2;
    
    /**
     * An error occured while reading a short int.
     * 
     * @var integer
     */
    const SHORT = 3;
    
    /**
     * An error occured while reading an int.
     * 
     * @var integer
     */
    const INT = 4;
    
    /**
     * An error occured while reading a float.
     * 
     * @var integer
     */
    const FLOAT = 5;
    
    /**
     * An error occured while reading a boolean.
     * 
     * @var integer
     */
    const BOOLEAN = 6;
    
    /**
     * An error occured while reading a string.
     * 
     * @var integer
     */
    const STRING = 7;
    
    /**
     * An error occured while reading binary data.
     * 
     * @var integer
     */
    const BINARY = 8;
    
    /**
     * Create a new BinaryStreamReaderException object.
     *
     * @param string $message A human-readable message.
     * @param AbstractStreamReader $source The StreamReader object that caused the error.
     * @param integer $code An exception code. See class constants.
     * @param Exception $previous An optional previously thrown exception.
     */
    public function __construct($message, BinaryStreamReader $source, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->setSource($source);
    }
    
    /**
     * Get the BinaryStreamReader object that caused the error.
     *
     * @param BinaryStreamReader $source A BinaryStreamReader object.
     */
    protected function setSource(BinaryStreamReader $source) {
        $this->source = $source;
    }
    
    /**
     * Set the BinaryStreamReader object that caused the error.
     *
     * @return BinaryStreamReader A BinaryStreamReader object.
     */
    public function getSource() {
        return $this->source;
    }
}