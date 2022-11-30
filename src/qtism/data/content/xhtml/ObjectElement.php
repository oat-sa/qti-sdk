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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2021 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\xhtml;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\content\BodyElement;
use qtism\data\content\FlowStatic;
use qtism\data\content\FlowTrait;
use qtism\data\content\InlineStatic;
use qtism\data\content\ObjectFlowCollection;
use qtism\data\QtiComponentCollection;

/**
 * From IMS QTI:
 *
 * The object QTI class.
 */
class ObjectElement extends BodyElement implements FlowStatic, InlineStatic
{
    use FlowTrait;

    /**
     * The content elements of the object.
     *
     * @var ObjectFlowCollection
     */
    private $content;

    /**
     * From IMS QTI:
     *
     * The data attribute provides a URI for locating the data
     * associated with the object.
     *
     * @var string
     * @qtism-bean-property
     */
    private $data;

    /**
     * The mime-type.
     *
     * @var string
     * @qtism-bean-property
     */
    private $type;

    /**
     * The object's width attribute.
     *
     * The value of this attribute can be:
     * * a string: a percentage e.g. "10%" or a length in pixels e.g. 10
     * * null: no width is set
     *
     * @var string|null
     * @qtism-bean-property
     */
    private $width;

    /**
     * The object's height attribute.
     *
     * The value of this attribute can be:
     * * a string: a percentage e.g. "10%" or a length in pixels e.g. 10
     * * null: no height is set
     *
     * @var string|null
     * @qtism-bean-property
     */
    private $height;

    /**
     * Create a new ObjectElement object.
     *
     * @param string $data The URI for locating the data of the object.
     * @param string $type The mime-type of the object.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If any of the arguments above is invalid.
     */
    public function __construct(string $data, $type, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($id, $class, $lang, $label);
        $this->setData($data);
        $this->setType($type);
        $this->setContent(new ObjectFlowCollection());
    }

    /**
     * Set the URI for locating the data of the object.
     *
     * @param string $data The URI for locating the data of the object.
     * @throws InvalidArgumentException If $data is not a URI.
     */
    public function setData(string $data): void
    {
        if ((is_string($data) && $data === '') || Format::isUri($data) === true) {
            $this->data = $data;
        } else {
            $msg = "The 'data' argument must be a URI or an empty string, '" . gettype($data) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the URI for locating the data of the object.
     *
     * @return string A URI.
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * Set the mime-type of the object.
     *
     * @param string $type A mime-type.
     * @throws InvalidArgumentException If $type is not a valid mime-type.
     */
    public function setType($type): void
    {
        if (is_string($type) && empty($type) === false) {
            $this->type = $type;
        } else {
            $msg = "The 'type' argument must be a non-empty string, '" . gettype($type) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the mime-type of the object.
     *
     * @return string A mime-type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the width of the object. A null value unsets width.
     *
     * @param mixed $width
     * @throws InvalidArgumentException when $width is not valid.
     */
    public function setWidth($width): void
    {
        $this->width = Format::sanitizeXhtmlLength($width, 'width');
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function hasWidth(): bool
    {
        return $this->width !== null;
    }

    /**
     * Set the height of the object. A null value unsets height.
     *
     * @param mixed $height
     * @throws InvalidArgumentException when $height is not valid.
     */
    public function setHeight($height): void
    {
        $this->height = Format::sanitizeXhtmlLength($height, 'height');
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function hasHeight(): bool
    {
        return $this->height !== null;
    }

    /**
     * Get the components composing the Object.
     *
     * @return ObjectFlowCollection|QtiComponentCollection A collection of ObjectFlow objects.
     */
    public function getComponents(): QtiComponentCollection
    {
        return $this->getContent();
    }

    /**
     * Set the components composing the ObjectElement.
     *
     * @param ObjectFlowCollection $content
     */
    public function setContent(ObjectFlowCollection $content): void
    {
        $this->content = $content;
    }

    /**
     * Get the components composing the ObjectElement.
     *
     * @return ObjectFlowCollection
     */
    public function getContent(): ObjectFlowCollection
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'object';
    }
}
