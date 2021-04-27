<?php

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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\xhtml;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\content\AtomicInline;

/**
 * The XHTML img class.
 */
class Img extends AtomicInline
{
    /**
     * The img's src attribute.
     *
     * @var string
     * @qtism-bean-property
     */
    private $src;

    /**
     * The img's src attribute.
     *
     * @var string
     * @qtism-bean-property
     */
    private $alt;

    /**
     * The img's longdesc attribute.
     *
     * @var string
     * @qtism-bean-property
     */
    private $longdesc = '';

    /**
     * The image's height attribute.
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
     * The image's width attribute.
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
     * Create a new Img object.
     *
     * @param string $src A URI.
     * @param string $alt An alternative text.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The lang of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If one of the argument is invalid.
     */
    public function __construct(string $src, $alt, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($id, $class, $lang, $label);
        $this->setSrc($src);
        $this->setAlt($alt);
        $this->setLongdesc('');
    }

    /**
     * Set the src attribute.
     *
     * @param string $src A URI.
     * @throws InvalidArgumentException If $src is not a valid URI.
     */
    public function setSrc(string $src): void
    {
        if (Format::isUri($src) === true) {
            $this->src = $src;
        } else {
            $msg = "The 'src' argument must be a valid URI, '" . $src . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the src attribute.
     *
     * @return string A URI.
     */
    public function getSrc()
    {
        return $this->src;
    }

    /**
     * Set the alt attribute.
     *
     * @param string $alt A string
     * @throws InvalidArgumentException If $alt is not a string.
     */
    public function setAlt($alt)
    {
        if (is_string($alt)) {
            $this->alt = $alt;
        } else {
            $msg = "The 'alt' argument must be a string, '" . gettype($alt) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the value of the alt attribute.
     *
     * @return string A non-empty string.
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Get the longdesc attribute.
     *
     * @param string $longdesc A valid URI.
     * @throws InvalidArgumentException If $longdesc is not a valid URI.
     */
    public function setLongdesc(string $longdesc)
    {
        if ($longdesc === '' || Format::isUri($longdesc)) {
            $this->longdesc = $longdesc;
        } else {
            $msg = "The 'longdesc' argument must be a valid URI, '" . $longdesc . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the longdesc attribute.
     *
     * @return string A URI.
     */
    public function getLongdesc()
    {
        return $this->longdesc;
    }

    /**
     * Whether a longdesc attribute is defined.
     *
     * @return bool
     */
    public function hasLongdesc()
    {
        return $this->getLongdesc() !== '';
    }

    /**
     * Set the height of the image.
     *
     * A null value means that no height is provided.
     *
     * @param mixed $height A height.
     * @throws InvalidArgumentException when $height is not valid.
     */
    public function setHeight($height): void
    {
        $this->height = Format::sanitizeXhtmlLength($height, 'height');
    }

    /**
     * Get the height of the image.
     * Null means that no height is set.
     */
    public function getHeight(): ?string
    {
        return $this->height;
    }

    /**
     * Whether a height is set.
     */
    public function hasHeight(): bool
    {
        return $this->height !== null;
    }

    /**
     * Set the width of the image.
     * A null value means that no width is set.
     *
     * @param mixed $width A width.
     * @throws InvalidArgumentException when $width is not valid.
     */
    public function setWidth($width): void
    {
        $this->width = Format::sanitizeXhtmlLength($width, 'width');
    }

    /**
     * Get the width of the image.
     * Null means that no width is set.
     */
    public function getWidth(): ?string
    {
        return $this->width;
    }

    /**
     * Whether a width is set.
     */
    public function hasWidth(): bool
    {
        return $this->width !== null;
    }

    /**
     * @return string
     */
    public function getQtiClassName()
    {
        return 'img';
    }
}
