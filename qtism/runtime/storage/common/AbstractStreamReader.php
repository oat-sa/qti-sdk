<?php

namespace qtism\runtime\storage\common;

/**
 * The common base class for all StreamReader implementations.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class AbstractStreamReader {
    
    /**
     * The IStream object to read.
     *
     * @var IStream.
     */
    private $stream;
    
    /**
     * Create a new AbstractStreamReader object.
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
            $msg = "A StreamReader do not accept closed streams to be read.";
            throw new StreamReaderException($msg, $this, StreamReaderException::NOT_OPEN);
        }
    
        $this->stream = $stream;
    }
}