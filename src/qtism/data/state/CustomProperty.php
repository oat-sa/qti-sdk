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
 * Represents a single <customProperty> element in assessmentTest metadata.
 */
class CustomProperty
{
    /** @var string */
    private $uri;
    /** @var string|null */
    private $label;
    /** @var string|null */
    private $domain;
    /** @var string|null */
    private $checksum;
    /** @var string|null */
    private $widget;
    /** @var string|null */
    private $alias;
    /** @var string|null */
    private $multiple;
    /** @var string|null */
    private $scale;

    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(?string $domain): void
    {
        $this->domain = $domain;
    }

    public function getChecksum(): ?string
    {
        return $this->checksum;
    }

    public function setChecksum(?string $checksum): void
    {
        $this->checksum = $checksum;
    }

    public function getWidget(): ?string
    {
        return $this->widget;
    }

    public function setWidget(?string $widget): void
    {
        $this->widget = $widget;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): void
    {
        $this->alias = $alias;
    }

    public function getMultiple(): ?string
    {
        return $this->multiple;
    }

    public function setMultiple(?string $multiple): void
    {
        $this->multiple = $multiple;
    }

    public function getScale(): ?string
    {
        return $this->scale;
    }

    public function setScale(?string $scale): void
    {
        $this->scale = $scale;
    }
} 