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
 */

namespace qtism\data\state;

/**
 * Represents the <metadata> element in assessmentTest, containing custom properties.
 */
class MetaData
{
    /** @var CustomProperty[] */
    private $customProperties = [];

    /**
     * @return CustomProperty[]
     */
    public function getCustomProperties(): array
    {
        return $this->customProperties;
    }

    public function setCustomProperties(array $customProperties): void
    {
        $this->customProperties = $customProperties;
    }

    public function addCustomProperty(CustomProperty $property): void
    {
        $this->customProperties[] = $property;
    }
} 