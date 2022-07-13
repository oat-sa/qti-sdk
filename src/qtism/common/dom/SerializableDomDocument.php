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
 * Copyright (c) 2017-2022 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\common\dom;

use DOMDocument;
use Exception;

/**
 * Serializable DOM Document
 *
 * This class is a PHP Serializable DOMDocument implementation.
 * @serializable
 */
class SerializableDomDocument
{
    private DOMDocument $dom;

    public function __construct(string $version = '1.0', string $encoding = '')
    {
        $this->dom = new DOMDocument($version, $encoding);
    }

    public function __serialize(): array
    {
        return [
            'version'  => $this->dom->xmlVersion,
            'encoding' => $this->dom->encoding,
            'xmlData'  => (string)$this->__toString()
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->dom = new DOMDocument($data['version'], $data['encoding']);
        $this->dom->loadXML($data['xmlData']);
    }

    public function __toString(): string
    {
        return $this->dom->saveXML();
    }

    /**
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        if (!method_exists($this->dom, $name)) {
            throw new Exception(__CLASS__ . 'has no method::' . $name);
        }

        return call_user_func_array([$this->dom, $name], $arguments);
    }

    public function __get($name)
    {
        if (!property_exists($this->dom, $name)) {
            throw new Exception(__CLASS__ . 'has no property::' . $name);
        }

        return $this->dom->$name ?? null;
    }

    public function __set($name, $value)
    {
        if (!property_exists($this->dom, $name)) {
            throw new Exception(__CLASS__ . 'has no property::' . $name);
        }
        $this->dom->$name = $value;

        return $this->dom;
    }
}
