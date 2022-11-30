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

/**
 * From IMS QTI:
 *
 * Some of the graphic interactions involve images with
 * specially defined areas or hotspots.
 */
interface Hotspot
{
    /**
     * Set the shape of the hotspot.
     *
     * @param int $shape A value from the Shape enumeration.
     * @throws InvalidArgumentException If $shape is not a value from the Shape enumeration.
     */
    public function setShape($shape);

    /**
     * Get the shape of the hotspot.
     *
     * @return QtiShape|null A Shape object.
     */
    #[\ReturnTypeWillChange]
    public function getShape();

    /**
     * Set the coords of the hotspot.
     *
     * @param QtiCoords $coords A Coords object.
     */
    public function setCoords(QtiCoords $coords);

    /**
     * Get the coords of the hotspot.
     *
     * @return QtiCoords A Coords object.
     */
    public function getCoords(): QtiCoords;

    /**
     * Set the alternative text for this hotspot.
     *
     * @param string $hotspotLabel A string with a maximum of 256 characters.
     * @throws InvalidArgumentException If $hotspotLabel is larger than 256 characters.
     */
    public function setHotspotLabel($hotspotLabel);

    /**
     * Get the alternative text for this hotspot.
     *
     * @return string A string with a maximum of 256 characters.
     */
    public function getHotspotLabel(): string;

    /**
     * Whether or not a value is defined for the hotspotLabel
     * attribute.
     *
     * @return bool
     */
    public function hasHotspotLabel(): bool;
}
