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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\interactions;

use InvalidArgumentException;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\common\utils\Format;
use qtism\data\QtiComponentCollection;

/**
 * The QTI HotspotChoice class.
 */
class HotspotChoice extends Choice implements Hotspot
{
    /**
     * From IMS QTI:
     *
     * The shape of the hotspot.
     *
     * @var QtiShape
     * @qtism-bean-property
     */
    private $shape;

    /**
     * From IMS QTI:
     *
     * The size and position of the hotspot, interpreted in conjunction with the shape.
     *
     * @var QtiCoords
     * @qtism-bean-property
     */
    private $coords;

    /**
     * From IMS QTI:
     *
     * The alternative text for this (hot) area of the image, if specified it must be
     * treated in the same way as alternative text for img. For hidden hotspots this
     * label is ignored.
     *
     * @var string
     * @qtism-bean-property
     */
    private $hotspotLabel = '';

    /**
     * Create a new HotspotChoice object.
     *
     * @param string $identifier The identifier of the choice.
     * @param int $shape A value from the Shape enumeration
     * @param QtiCoords $coords The size and position of the hotspot, interpreted in conjunction with $shape.
     * @param string $id The identifier of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The lang of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If one of the argument is invalid.
     */
    public function __construct($identifier, $shape, QtiCoords $coords, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($identifier, $id, $class, $lang, $label);
        $this->setShape($shape);
        $this->setCoords($coords);
    }

    /**
     * Set the shape of the associableHotspot.
     *
     * @param int $shape A value from the Shape enumeration.
     */
    public function setShape($shape): void
    {
        if (in_array($shape, QtiShape::asArray())) {
            $this->shape = $shape;
        } else {
            $msg = "The 'shape' argument must be a value from the Shape enumeration, '" . $shape . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the shape of the associableHotspot.
     *
     * @return QtiShape A Shape object.
     */
    public function getShape()
    {
        return $this->shape;
    }

    /**
     * Set the coords of the associableHotspot.
     *
     * @param QtiCoords $coords A QtiCoords object.
     */
    public function setCoords(QtiCoords $coords): void
    {
        $this->coords = $coords;
    }

    /**
     * Get the coords of the associableHotspot.
     *
     * @return QtiCoords A QtiCoords object.
     */
    public function getCoords(): QtiCoords
    {
        return $this->coords;
    }

    /**
     * Set the hotspotLabel of the associableHotspot.
     *
     * @param string $hotspotLabel A string with at most 256 characters.
     * @throws InvalidArgumentException If $hotspotLabel has more than 256 characters.
     */
    public function setHotspotLabel($hotspotLabel): void
    {
        if (Format::isString256($hotspotLabel) === true) {
            $this->hotspotLabel = $hotspotLabel;
        } else {
            $msg = "The 'hotspotLabel' argument must be a string value with at most 256 characters.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the hotspotLabel of the associableHotspot.
     *
     * @return string A string with at most 256 characters.
     */
    public function getHotspotLabel(): string
    {
        return $this->hotspotLabel;
    }

    /**
     * Whether or not a value is defined for the hotspotLabel attribute.
     *
     * @return bool
     */
    public function hasHotspotLabel(): bool
    {
        return $this->getHotspotLabel() !== '';
    }

    /**
     * HotspotChoice components are not composite. Then, this method
     * systematically returns an empty collection.
     *
     * @return QtiComponentCollection An empty collection.
     */
    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection();
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'hotspotChoice';
    }
}
