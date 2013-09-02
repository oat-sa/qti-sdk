<?php

namespace qtism\runtime\storage\common;

use \Exception;

class StreamReaderException extends Exception {
    
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
     * The StreamReader that caused the error.
     *
     * @var StreamReader
     */
    private $source;
    
    /**
     * Create a new StreamReaderException object.
     *
     * @param string $message A human-readable message.
     * @param AbstractStreamReader $source The StreamReader object that caused the error.
     * @param integer $code An exception code. See class constants.
     * @param Exception $previous An optional previously thrown exception.
     */
    public function __construct($message, AbstractStreamReader $source, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->setSource($source);
    }
    
    /**
     * Get the StreamReader object that caused the error.
     *
     * @param StreamReader $source A StreamReader object.
     */
    protected function setSource(AbstractStreamReader $source) {
        $this->source = $source;
    }
    
    /**
     * Set the StreamReader object that caused the error.
     *
     * @return StreamReader A StreamReader object.
     */
    public function getSource() {
        return $this->source;
    }
}