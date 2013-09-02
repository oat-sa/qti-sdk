<?php

namespace qtism\runtime\storage\common;

/**
 * The interface a class able to read a Stream must implement.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
interface IStream {
    
    /**
     * Open the stream.
     * 
     * @throws StreamException If an error occurs while opening the stream. The error code will be StreamException::OPEN or StreamException::ALREADY_OPEN.
     */
    public function open();
    
    /**
     * Whether the stream is open.
     * 
     * @return boolean
     */
    public function isOpen();
    
    /**
     * Write $data into the stream.
     * 
     * @param string $data The data to be written in the stream.
     * @return integer The length of the written $data.
     * @throws StreamException If an error occurs while writing the stream. The error code will be StreamException::WRITE or StreamException::NOT_OPEN.
     */
    public function write($data);
    
    /**
     * Close the stream.
     * 
     * @throws StreamException If an error occurs while closing the stream. The error code will be StreamException::CLOSE or StreamException::NOT_OPEN.
     */
    public function close();
    
    /**
     * Read $length bytes from the stream.
     * 
     * @param integer $length The length in bytes of the data to be read from the stream.
     * @throws StreamException If an error occurs while reading the stream. The error code will be StreamException::READ or StreamException::NOT_OPEN.
     */
    public function read($length);
    
    /**
     * Rewind the stream as its beginning.
     * 
     * @throws StreamException If an error occurs during the rewind call. The error code will be StreamException::REWIND or StreamException::NOT_OPEN.
     */
    public function rewind();
    
    /**
     * Whether the end of the stream is reached.
     * 
     * @return boolean
     * @throws StreamException If the stream is not open. The error code will be StreamException::NOT_OPEN;
     */
    public function eof();
}