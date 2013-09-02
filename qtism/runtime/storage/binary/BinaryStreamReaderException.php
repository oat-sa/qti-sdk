<?php

namespace qtism\runtime\storage\binary;

use qtism\runtime\storage\common\StreamReaderException;

/**
 * The BinaryStreamReaderException class represents the error
 * that could occur while reading/extracting data from a BinaryStream
 * object.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BinaryStreamReaderException extends StreamReaderException {
    
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
    
}