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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\storage;

/**
 * The MemoryStream class represents a binary stream based on
 * an in-memory binary string.
 */
class MemoryStream implements IStream
{
    /**
     * The binary string to be read.
     *
     * @var string
     */
    private $binary = '';

    /**
     * Whether the stream is open.
     *
     * @var boolean
     */
    private $open = false;

    /**
     * The position in the stream.
     *
     * @var integer
     */
    private $position = 0;

    private $length = 0;

    /**
     * Create a new MemoryStream object.
     *
     * @param string $binary A binary string.
     */
    public function __construct($binary = '')
    {
        $this->setBinary($binary);
        $this->setLength(strlen($binary));
    }

    /**
     * Set the binary string that is composing the data stream.
     *
     * @param string $binary A binary string.
     */
    protected function setBinary($binary)
    {
        $this->binary = $binary;
    }

    /**
     * Get the binary string that is composing the data stream.
     *
     * @return string A binary string.
     */
    public function getBinary()
    {
        return $this->binary;
    }

    /**
     * Returns the current position in the stream.
     *
     * @return integer The position in the stream. Position begins at 0.
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set the current position in the stream.
     *
     * @param integer $position A position in the stream to be set.
     */
    protected function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Set the length of the binary data.
     *
     * @param integer $length
     */
    protected function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * Get the length of the binary data.
     *
     * @return integer
     */
    public function getLength()
    {
        return $this->length;
    }

    protected function incrementLength($i)
    {
        $this->length += $i;
    }

    /**
     * Increment the current position by $i.
     *
     * @param integer $i The increment to be applied on the current position in the stream.
     */
    protected function incrementPosition($i)
    {
        $this->position += $i;
    }

    /**
     * Open the binary stream.
     *
     * @throws MemoryStreamException If the stream is already opened.
     */
    public function open()
    {
        if ($this->isOpen() === true) {
            $msg = "The MemoryStream is already open.";
            throw new MemoryStreamException($msg, $this, MemoryStreamException::ALREADY_OPEN);
        }

        $this->setOpen(true);
    }

    /**
     * Close the binary stream.
     *
     * @throws MemoryStreamException If the stream is closed prior the call.
     */
    public function close()
    {
        if ($this->isOpen() === false) {
            $msg = "Cannot call close() a closed stream.";
            throw new MemoryStreamException($msg, $this, MemoryStreamException::NOT_OPEN);
        }

        $this->setOpen(false);
    }

    /**
     * Read $length bytes from the MemoryStream.
     *
     * @param integer $length The number of bytes to read.
     * @return string The read value or an empty string if length = 0.
     * @throws MemoryStreamException If the read is out of the bounds of the stream e.g. EOF reach.
     */
    public function read($length)
    {
        if ($this->isOpen() === false) {
            $msg = "Cannot read from a closed MemoryStream.";
            throw new MemoryStreamException($msg, $this, MemoryStreamException::NOT_OPEN);
        }

        if ($length === 0) {
            return '';
        }

        $position = $this->position;
        $finalPosition = $position + $length;

        if ($finalPosition > $this->length) {
            $msg = "Cannot read outside the bounds of the MemoryStream.";
            throw new MemoryStreamException($msg, $this, MemoryStreamException::READ);
        }

        $this->incrementPosition($length);

        $returnValue = '';

        while ($position < $finalPosition) {
            $returnValue .= $this->binary[$position];
            $position++;
        }

        return $returnValue;
    }

    /**
     * Write some $data in the stream.
     *
     * @param string $data
     * @return integer The amount of written bytes.
     * @throws MemoryStreamException
     */
    public function write($data)
    {
        if ($this->isOpen() === false) {
            $msg = "Cannot write in a closed MemoryStream.";
            throw new MemoryStreamException($msg, $this, MemoryStreamException::NOT_OPEN);
        }

        if ($this->length - 1 === $this->position) {
            // simply append.
            $this->binary .= $data;
        } elseif ($this->position === 0) {
            // simply prepend.
            $this->binary = ($data . $this->binary);
        } else {
            // we are in the middle of the string.
            $part1 = substr($this->binary, 0, $this->position);
            $part2 = substr($this->binary, $this->position);
            $this->binary = ($part1 . $data . $part2);
        }

        $dataLen = strlen($data);
        $this->incrementPosition($dataLen);
        $this->incrementLength($dataLen);

        return $dataLen;
    }

    /**
     * Whether the end of the binary stream is reached.
     *
     * @return boolean
     */
    public function eof()
    {
        return $this->isOpen() === false || $this->getPosition() >= $this->getLength();
    }

    /**
     * Whether the stream is open yet.
     *
     * @return boolean
     */
    public function isOpen()
    {
        return $this->open;
    }

    /**
     * Rewind the stream to its initial position.
     *
     * @throws MemoryStreamException If the binary stream is not open.
     */
    public function rewind()
    {
        if ($this->isOpen() === false) {
            $msg = "Cannot call rewind() on a closed MemoryStream.";
            throw new MemoryStreamException($msg, $this, MemoryStreamException::NOT_OPEN);
        }

        $this->setPosition(0);
    }

    /**
     * Specify whether or not the stream is open.
     *
     * @param boolean $open
     */
    protected function setOpen($open)
    {
        $this->open = $open;
    }

    /**
     * Flush the whole stream.
     *
     * @throws MemoryStreamException If the binary stream is closed.
     */
    public function flush()
    {
        if ($this->isOpen() === true) {
            $this->setBinary('');
            $this->rewind();
        } else {
            $msg = "Cannot flush a closed MemoryStream.";
            throw new MemoryStreamException($msg, $this, MemoryStreamException::NOT_OPEN);
        }
    }
}
