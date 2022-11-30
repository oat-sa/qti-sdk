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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\datatypes\files;

use JsonSerializable;
use qtism\common\datatypes\QtiFile;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

/**
 * An implementation of File storing only a hash of the file.
 * File contents has to be previously persisted externally.
 * Hash string will serve the purpose of comparing files only.
 */
class FileHash implements QtiFile, JsonSerializable
{
    /**
     * Key to use in json payload to trigger the storage of a file hash instead
     * of a hash.
     */
    public const FILE_HASH_KEY = 'fileHash';

    /**
     * The id of the file on the persistent storage.
     *
     * @var string
     */
    private $id;

    /**
     * The MIME type of the file content.
     *
     * @var string
     */
    private $mimeType;

    /**
     * The original file name.
     *
     * @var string
     */
    private $filename;

    /**
     * The hash of the file contents.
     *
     * @var string
     */
    private $hash;

    /**
     * Create a new FileHash object.
     *
     * @param string $id The id of the file in the external file store.
     * @param string $mimeType The mime-type of the file.
     * @param string $filename The name of the original file.
     * @param string $hash The hash of the file.
     */
    public function __construct($id, $mimeType, $filename, $hash)
    {
        $this->setId($id);
        $this->setMimeType($mimeType);
        $this->setFilename($filename);
        $this->setHash($hash);
    }

    /**
     * Create a new FileHash object from an array of properties.
     *
     * @param array $properties
     * @return FileHash
     */
    public static function createFromArray(array $properties): self
    {
        return new self(
            $properties['id'],
            $properties['mime'],
            $properties['name'],
            $properties['data']
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'mime' => $this->mimeType,
            'name' => $this->filename,
            'data' => $this->hash,
        ];
    }

    /**
     * Set the id of the file.
     *
     * @param string $id
     */
    protected function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * Get the id of the file.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the mime-type of the file.
     *
     * @param string $mimeType
     */
    protected function setMimeType($mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Get the mime-type of the file.
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Get the name of the file.
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Set the name of the file.
     *
     * @param string $filename
     */
    protected function setFilename($filename): void
    {
        $this->filename = $filename;
    }

    /**
     * Get the sequence of bytes composing the hash of the file.
     */
    public function getData(): string
    {
        return $this->getHash();
    }

    /**
     * Returns nothing because the content of the file is stored externally
     * and thus not accessible in here.
     */
    public function getStream(): void
    {
    }

    /**
     * Get the hash of the file.
     *
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Set the hash of the file.
     *
     * @param string $hash
     */
    protected function setHash($hash): void
    {
        $this->hash = $hash;
    }

    /**
     * Get the cardinality of the File value.
     *
     * @return int A value from the Cardinality enumeration.
     */
    public function getCardinality(): int
    {
        return Cardinality::SINGLE;
    }

    /**
     * Get the baseType of the File value.
     *
     * @return int A value from the BaseType enumeration.
     */
    public function getBaseType(): int
    {
        return BaseType::FILE;
    }

    /**
     * Whether or not the File has a file name.
     *
     * @return bool
     */
    public function hasFilename(): bool
    {
        return true;
    }

    /**
     * Whether or not two File objects are equals. Two File values
     * are considered to be identical if they have the same file name,
     * mime-type and hash.
     *
     * @param mixed $obj
     * @return bool
     */
    public function equals($obj): bool
    {
        if (!$obj instanceof self) {
            return false;
        }

        return $this->getId() === $obj->getId()
            && $this->getMimeType() === $obj->getMimeType()
            && $this->getFilename() === $obj->getFilename()
            && $this->getHash() === $obj->getHash();
    }

    /**
     * Get the unique identifier of the File.
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->getId();
    }

    /**
     * File as a string
     *
     * Returns the file name.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFilename();
    }
}
