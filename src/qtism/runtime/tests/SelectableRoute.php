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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\tests;

/**
 * The SelectableRoute class aims at representing a Route which is
 * subject to be selected in a selection process.
 */
class SelectableRoute extends Route
{
    /**
     * If the SelectableRoute is fixed.
     *
     * @var bool
     */
    private $visible;

    /**
     * If the SelectableRoute is visible.
     *
     * @var bool
     */
    private $fixed;

    /**
     * If the SelectableRoute is required.
     *
     * @var bool
     */
    private $required;

    /**
     * If the RouteItems must be kept together.
     *
     * @var bool
     */
    private $keepTogether;

    /**
     * Create a new SelectableRoute object.
     *
     * @param bool $fixed If the SelectableRoute is fixed.
     * @param bool $required If the SelectableRoutei is required.
     * @param bool $visible If the SelectableRoute is visible.
     * @param bool $keepTogether If the SelectableRoute must be kept together.
     */
    public function __construct($fixed = false, $required = false, $visible = true, $keepTogether = true)
    {
        parent::__construct();
        $this->setFixed($fixed);
        $this->setRequired($required);
        $this->setVisible($visible);
        $this->setKeepTogether($keepTogether);
    }

    /**
     * Whether the SelectableRoute is fixed.
     *
     * @return bool
     */
    public function isFixed(): bool
    {
        return $this->fixed;
    }

    /**
     * Whether the SelectableRoute is visible.
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Whether the SelectableRoute is required.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Set whether the SelectableRoute is fixed.
     *
     * @param bool $fixed
     */
    public function setFixed($fixed): void
    {
        $this->fixed = $fixed;
    }

    /**
     * Set whether the SelectableRoute is visible.
     *
     * @param bool $visible
     */
    public function setVisible($visible): void
    {
        $this->visible = $visible;
    }

    /**
     * Set Whether the SelectableRoute is required.
     *
     * @param bool $required
     */
    public function setRequired($required): void
    {
        $this->required = $required;
    }

    /**
     * Set whether or not the RouteItem objects held by the Route must be kept together.
     *
     * @param bool $keepTogether
     */
    public function setKeepTogether($keepTogether): void
    {
        $this->keepTogether = $keepTogether;
    }

    /**
     * Whether the RouteItem objects held by the Route must be kept together.
     *
     * @return bool
     */
    public function mustKeepTogether(): bool
    {
        return $this->keepTogether;
    }
}
