<?php

declare(strict_types=1);

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

use DateTime;
use DateTimeZone;

/**
 * The BinaryStreamAccess aims at providing the needed methods to
 * easily read the data contained by BinaryStream objects.
 */
class BinaryStreamAccess extends AbstractStreamAccess
{
    /**
     * Set the IStream object to be read.
     *
     * @param IStream $stream An IStream object.
     * @throws StreamAccessException If the $stream is not open yet.
     */
    protected function setStream(IStream $stream): void
    {
        if ($stream->isOpen() === false) {
            $msg = 'A BinaryStreamAccess do not accept closed streams to be read.';
            throw new BinaryStreamAccessException($msg, $this, StreamAccessException::NOT_OPEN);
        }

        parent::setStream($stream);
    }

    /**
     * Read a single byte unsigned integer from the current binary stream.
     *
     * @return int
     * @throws BinaryStreamAccessException
     */
    public function readTinyInt(): int
    {
        try {
            $bin = $this->getStream()->read(1);

            return ord($bin);
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::TINYINT);
        }

        return -1;
    }

    /**
     * Write a single byte unsigned integer in the current binary stream.
     *
     * @param int $tinyInt
     * @throws BinaryStreamAccessException
     */
    public function writeTinyInt($tinyInt): void
    {
        try {
            $this->getStream()->write(chr($tinyInt));
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::TINYINT, false);
        }
    }

    /**
     * Read a 2 bytes unsigned integer from the current binary stream.
     *
     * @return int
     * @throws BinaryStreamAccessException
     */
    public function readShort(): int
    {
        try {
            $bin = $this->getStream()->read(2);

            return current(unpack('S', $bin));
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::SHORT);
        }

        return -1;
    }

    /**
     * Write a 2 bytes unsigned integer in the current binary stream.
     *
     * @param int $short
     * @throws BinaryStreamAccessException
     */
    public function writeShort($short): void
    {
        try {
            $this->getStream()->write(pack('S', $short));
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::SHORT, false);
        }
    }

    /**
     * Read a 8 bytes signed integer from the current binary stream.
     *
     * @return int
     * @throws BinaryStreamAccessException
     */
    public function readInteger(): int
    {
        try {
            $bin = $this->getStream()->read(4);

            return (int)current(unpack('l', $bin));
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::INT);
        }

        return -1;
    }

    /**
     * Write a 8 bytes signed integer in the current binary stream.
     *
     * @param int $int
     * @throws BinaryStreamAccessException
     */
    public function writeInteger($int): void
    {
        try {
            $this->getStream()->write(pack('l', $int));
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::INT, false);
        }
    }

    /**
     * Read a double precision float from the current binary stream.
     *
     * @return float
     * @throws BinaryStreamAccessException
     */
    public function readFloat(): float
    {
        try {
            $bin = $this->getStream()->read(8);

            return current(unpack('d', $bin));
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::FLOAT);
        }

        return 0.00;
    }

    /**
     * Write a double precision float in the current binary stream.
     *
     * @param float $float
     * @throws BinaryStreamAccessException
     */
    public function writeFloat($float): void
    {
        try {
            $this->getStream()->write(pack('d', $float));
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::FLOAT, false);
        }
    }

    /**
     * Read a boolean value from the current binary stream.
     *
     * @return bool
     * @throws BinaryStreamAccessException
     */
    public function readBoolean(): bool
    {
        try {
            $int = ord($this->getStream()->read(1));

            return ($int === 0) ? false : true;
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::BOOLEAN);
        }

        return false;
    }

    /**
     * Write a boolean value from the current binary stream.
     *
     * @param bool $boolean
     * @throws BinaryStreamAccessException
     */
    public function writeBoolean($boolean): void
    {
        try {
            $val = ($boolean === true) ? 1 : 0;
            $this->getStream()->write(chr($val));
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::FLOAT, false);
        }
    }

    /**
     * Read a string value from the current binary stream.
     *
     * @return string
     * @throws BinaryStreamAccessException
     */
    public function readString(): string
    {
        try {
            $binLength = $this->getStream()->read(2);
            $length = current(unpack('S', $binLength));

            return $this->getStream()->read($length);
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::STRING);
        }

        return '';
    }

    /**
     * Write a string value from in the current binary string.
     *
     * @param string $string
     * @throws BinaryStreamAccessException
     */
    public function writeString($string): void
    {
        // $maxLen = 2^16 -1 = max val of unsigned short integer.
        $maxLen = 65535;
        $len = strlen((string)$string);

        if ($len > $maxLen) {
            $len = $maxLen;
            $string = substr($string, 0, $maxLen);
        }

        try {
            $this->getStream()->write(pack('S', $len) . $string);
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::STRING, false);
        }
    }

    /**
     * Read binary data from the current binary stream.
     *
     * @return string A binary string.
     * @throws BinaryStreamAccessException
     */
    public function readBinary(): string
    {
        return $this->readString();
    }

    /**
     * Write binary data in the current binary stream.
     *
     * @param string $binary
     * @throws BinaryStreamAccessException
     */
    public function writeBinary($binary): void
    {
        $this->writeString($binary);
    }

    /**
     * Read a DateTime from the current binary stream.
     *
     * @return DateTime|null A DateTime object.
     * @throws BinaryStreamAccessException
     */
    public function readDateTime(): ?DateTime
    {
        try {
            $timeStamp = current(unpack('l', $this->getStream()->read(4)));
            $date = new DateTime('now', new DateTimeZone('UTC'));

            return $date->setTimestamp($timeStamp);
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::DATETIME);
        }

        return null;
    }

    /**
     * Write a DateTime from the current binary stream.
     *
     * @param DateTime $dateTime A DateTime object.
     * @throws BinaryStreamAccessException
     */
    public function writeDateTime(DateTime $dateTime): void
    {
        try {
            $timeStamp = $dateTime->getTimestamp();
            $this->getStream()->write(pack('l', $timeStamp));
        } catch (StreamException $e) {
            $this->handleBinaryStreamException($e, BinaryStreamAccessException::DATETIME, false);
        }
    }

    /**
     * Handle a BinaryStreamException in order to throw the relevant BinaryStreamAccessException.
     *
     * @param StreamException $e The StreamException object to deal with.
     * @param int $typeError The BinaryStreamAccess exception code to be thrown in case of error.
     * @param bool $read Whether or not the error occurred in a reading/writing context.
     * @throws BinaryStreamAccessException The resulting BinaryStreamAccessException.
     */
    protected function handleBinaryStreamException(StreamException $e, $typeError, $read = true): void
    {
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

            case BinaryStreamAccessException::DATETIME:
                $strType = 'datetime';
                break;
        }

        $strAction = ($read === true) ? 'reading' : 'writing';

        switch ($e->getCode()) {
            case StreamException::NOT_OPEN:
                $strAction = ucfirst($strAction);
                $msg = "${strAction} a ${strType} from a closed binary stream is not permitted.";
                throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::NOT_OPEN, $e);
                break;

            case StreamException::READ:
                $msg = "An error occurred while ${strAction} a ${strType}.";
                throw new BinaryStreamAccessException($msg, $this, $typeError, $e);
                break;

            default:
                $msg = "An unknown error occurred while ${strAction} a ${strType}.";
                throw new BinaryStreamAccessException($msg, $this, BinaryStreamAccessException::UNKNOWN, $e);
                break;
        }
    }
}
