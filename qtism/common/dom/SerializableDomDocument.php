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

use DOMAttr;
use DOMCDATASection;
use DOMComment;
use DOMDocument;
use DOMDocumentFragment;
use DOMDocumentType;
use DOMElement;
use DOMEntityReference;
use DOMImplementation;
use DOMNode;
use DOMNodeList;
use DOMProcessingInstruction;
use DOMText;
use Error;

/**
 * Serializable DOM Document
 *
 * This class is a PHP Serializable DOMDocument implementation.
 *
 * @property string|null $actualEncoding
 * @property $config
 * @property DOMDocumentType|null $doctype
 * @property DOMElement|null $documentElement
 * @property string|null $documentURI
 * @property string|null $encoding
 * @property DOMImplementation $implementation
 * @property bool $preserveWhiteSpace
 * @property bool $recover
 * @property bool $resolveExternals
 * @property bool $standalone
 * @property bool $strictErrorChecking
 * @property bool $substituteEntities
 * @property bool $validateOnParse
 * @property string|null $version
 * @property string|null $xmlEncoding
 * @property bool $xmlStandalone
 * @property string|null $xmlVersion
 * @property int $childElementCount
 * @property DOMElement|null $lastElementChild
 * @property DOMElement|null $firstElementChild
 *
 * @method createElement(string $localName, string $value)
 * @method DOMDocumentFragment createDocumentFragment()
 * @method DOMText|false createTextNode(string $data)
 * @method DOMComment|false createComment(string $data)
 * @method DOMCDATASection|false createCDATASection(string $data)
 * @method DOMProcessingInstruction|false createProcessingInstruction(string $target, string $data)
 * @method DOMAttr|false createAttribute(string $localName)
 * @method DOMEntityReference|false createEntityReference(string $name)
 * @method DOMNodeList|false getElementsByTagName(string $qualifiedName)
 * @method DOMNodeList|false importNode(DOMNode $node, bool $deep = false)
 * @method DOMElement|false createElementNS(string|null $namespace, string $qualifiedName, string $value)
 * @method DOMAttr|false createAttributeNS(string|null $namespace, string $qualifiedName)
 * @method DOMNodeList getElementsByTagNameNS(string|null $namespace, string $localName)
 * @method DOMElement|null getElementById(string $elementId)
 * @method DOMNode adoptNode(DOMNode $elementId)
 * @method append(...$nodes)
 * @method prepend(...$nodes)
 * @method normalizeDocument()
 * @method renameNode(DOMNode $node, $namespace, $qualifiedName)
 * @method DOMDocument|bool load(string $filename, ?int $options = null)
 * @method int|false save($filename, $options = null)
 * @method string|false saveXML(?DOMNode $node = null, int $options = null)
 * @method bool validate()
 * @method int|false xinclude(int $options = null)
 * @method DOMDocument|bool loadHTML(string $source, int $options=0)
 * @method DOMDocument|bool loadHTMLFile(string $filename, int $options=0)
 * @method string|false saveHTML(DOMNode $node = null)
 * @method int|false saveHTMLFile(string $filename)
 * @method bool schemaValidate($filename, $options = null)
 * @method bool schemaValidateSource($source, $flags)
 * @method bool relaxNGValidate(string $filename)
 * @method bool relaxNGValidateSource(string $source)
 * @method bool registerNodeClass(string $baseClass, string $extendedClass)
 */
class SerializableDomDocument
{
    /** need to keep php 7.1 support */
    private $xmlData;
    private $version;
    private $encoding;

    private $dom;

    public function __construct(string $version = '1.0', string $encoding = '')
    {
        $this->dom = new DOMDocument($version, $encoding);
    }

    public function __sleep()
    {
        $this->version = (string)$this->dom->xmlVersion;
        $this->encoding = (string)$this->dom->encoding;
        $this->xmlData = (string)$this;

        return ['version', 'encoding', 'xmlData'];
    }

    public function __wakeup()
    {
        $this->dom = new DOMDocument($this->version, $this->encoding);
        $this->dom->loadXML($this->xmlData);
    }

    public function __serialize(): array
    {
        return [
            'version'  => (string)$this->dom->xmlVersion,
            'encoding' => (string)$this->dom->encoding,
            'xmlData'  => (string)$this,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->dom = new DOMDocument($data['version'], $data['encoding']);
        $this->dom->loadXML($data['xmlData']);
    }

    public function __toString(): string
    {
        $xml = $this->dom->saveXML();
        return $xml ? : '';
    }

    public function __call($name, $arguments)
    {
        if (!method_exists($this->dom, $name)) {
            throw new Error(sprintf('Call to undefined method %s::%s()', __CLASS__, $name));
        }

        return call_user_func_array([$this->dom, $name], $arguments);
    }

    public function __get($name)
    {
        if (!property_exists($this->dom, $name)) {
            trigger_error(sprintf('Undefined property: %s::%s', __CLASS__, $name), E_USER_WARNING);
        }

        return $this->dom->$name ?? null;
    }

    public function __set($name, $value)
    {
        $this->dom->$name = $value;

        return $this->dom;
    }

    public function __isset(string $name): bool
    {
        return isset($this->dom->$name);
    }

    public function __unset(string $name): void
    {
        unset($this->dom->$name);
    }

    public function getDom(): DOMDocument
    {
        return $this->dom;
    }
}
