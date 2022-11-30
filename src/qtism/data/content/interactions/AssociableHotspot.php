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
use qtism\common\collections\IdentifierCollection;
use qtism\common\datatypes\QtiCoords;
use qtism\common\datatypes\QtiShape;
use qtism\common\utils\Format;
use qtism\data\QtiComponentCollection;

/**
 * The associableHotspot QTI class.
 */
class AssociableHotspot extends Choice implements AssociableChoice, Hotspot
{
    /**
     * From IMS QTI:
     *
     * The maximum number of choices this choice may be associated with.
     * If matchMax is 0 there is no restriction.
     *
     * @var int
     * @qtism-bean-property
     */
    private $matchMax;

    /**
     * From IMS QTI:
     *
     * The minimum number of choices this choice must be associated with to form a
     * valid response. If matchMin is 0 then the candidate is not required to
     * associate this choice with any others at all. matchMin must be less than or
     * equal to the limit imposed by matchMax.
     *
     * @var int
     * @qtism-bean-property
     */
    private $matchMin = 0;

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
     * From IMS QTI:
     *
     * A set of choices that this choice may be associated with, all others are
     * excluded. If no matchGroup is given, or if it is empty, then all other
     * choices may be associated with this one subject to their own matching
     * constraints.
     *
     * @var IdentifierCollection
     * @qtism-bean-property
     */
    private $matchGroup;

    /**
     * Create a new AssociableHotspot object.
     *
     * @param string $identifier The identifier of the associableHotspot.
     * @param int $matchMax The matchMax attribute.
     * @param int $shape A value of the Shape enumeration.
     * @param QtiCoords $coords The coords of the associableHotspot.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If one of the constructor's argument is invalid.
     */
    public function __construct($identifier, $matchMax, $shape, QtiCoords $coords, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($identifier, $id, $class, $lang, $label);
        $this->setMatchMax($matchMax);
        $this->setShape($shape);
        $this->setCoords($coords);
        $this->setMatchGroup(new IdentifierCollection());
    }

    /**
     * Set the matchMax of the associableHotspot.
     *
     * @param int $matchMax A positive (>= 0) integer.
     * @throws InvalidArgumentException If $matchMax is not a positive integer.
     */
    public function setMatchMax($matchMax): void
    {
        if (is_int($matchMax) && $matchMax >= 0) {
            $this->matchMax = $matchMax;
        } else {
            $msg = "The 'matchMax' argument must be a positive integer, '" . gettype($matchMax) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the matchMax of the associableHotspot.
     *
     * @return int A positive integer.
     */
    public function getMatchMax(): int
    {
        return $this->matchMax;
    }

    /**
     * Set the matchMin of the associableHotspot.
     *
     * @param int $matchMin A positive (>= 0) integer.
     * @throws InvalidArgumentException If $matchMin is not a positive integer.
     */
    public function setMatchMin($matchMin): void
    {
        if (is_int($matchMin) && $matchMin >= 0) {
            $this->matchMin = $matchMin;
        } else {
            $msg = "The 'matchMin' argument must be a positive integer, '" . gettype($matchMin) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the matchMin of the associableHotspot.
     *
     * @return int
     */
    public function getMatchMin(): int
    {
        return $this->matchMin;
    }

    /**
     * Set the shape of the associableHotspot.
     *
     * @param int $shape A value from the Shape enumeration.
     */
    public function setShape($shape): void
    {
        if (in_array($shape, QtiShape::asArray(), true)) {
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
    #[\ReturnTypeWillChange]
    public function getShape()
    {
        return $this->shape;
    }

    /**
     * Set the coords of the associableHotspot.
     *
     * @param QtiCoords $coords A Coords object.
     */
    public function setCoords(QtiCoords $coords): void
    {
        $this->coords = $coords;
    }

    /**
     * Get the coords of the associableHotspot.
     *
     * @return QtiCoords A Coords object.
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
     * Whether or not the associableHotspot has an hotspotLabel.
     *
     * @return bool
     */
    public function hasHotspotLabel(): bool
    {
        return $this->getHotspotLabel() !== '';
    }

    /**
     * @param IdentifierCollection $matchGroup
     */
    public function setMatchGroup(IdentifierCollection $matchGroup): void
    {
        $this->matchGroup = $matchGroup;
    }

    /**
     * @return IdentifierCollection
     */
    public function getMatchGroup(): IdentifierCollection
    {
        return $this->matchGroup;
    }

    /**
     * @return QtiComponentCollection
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
        return 'associableHotspot';
    }
}
