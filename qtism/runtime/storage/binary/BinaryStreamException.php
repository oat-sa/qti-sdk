<?php

namespace qtism\runtime\storage\binary;

use qtism\runtime\storage\common\IStream;
use qtism\runtime\storage\common\StreamException;
use \Exception;

/**
 * The BinaryStreamException represents errors that might occur while
 * dealing with a BinaryStream object.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BinaryStreamException extends StreamException {
    
    /**
     * Create a new BinaryStreamException.
     *
     * @param string $message The human-readable message describing the error.
     * @param BinaryStream $source The BinaryStream object where in the error occured.
     * @param integer $code A code describing the error.
     * @param Exception $previous An optional previous exception.
     */
    public function __construct($message, IStream $source,  $code = 0, Exception $previous = null) {
        parent::__construct($message, $source, $code, $previous);
    }
}