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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 * @license GPLv2
 * @package
 */

namespace qtism\data;

use \InvalidArgumentException;

/**
 * The Adaptive Item Selection class.
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 *
 */
class AdaptiveItemSelection extends SectionPart
{
    /**
     * Child elements.
     *
     * @var SectionPartCollection
     * @qtism-bean-property
     */
    private $sectionParts;

    /**
     * Create a new AdaptiveItemSelection object
     *
     * @param string $identifier A QTI Identifier.
     * @throws InvalidArgumentException If $identifier is not a valid QTI Identifier, $title is not a string, or visible is not a boolean.
     */
    public function __construct($identifier)
    {
        parent::__construct($identifier);
        $this->setSectionParts(new SectionPartCollection());
    }

    /**
     * Get the child elements.
     *
     * @return SectionPartCollection A collection of SectionPart objects.
     */
    public function getSectionParts()
    {
        return $this->sectionParts;
    }

    /**
     * Set the child elements.
     *
     * @param SectionPartCollection $sectionParts A collection of SectionPart objects.
     */
    public function setSectionParts(SectionPartCollection $sectionParts)
    {
        $this->sectionParts = $sectionParts;
    }

    /**
     * @return string
     */
    public function getQtiClassName()
    {
        return 'adaptiveItemSelection';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents()
    {
        $comp = array_merge(
            parent::getComponents()->getArrayCopy(),
            $this->getSectionParts()->getArrayCopy()
        );

        return new QtiComponentCollection($comp);
    }
}
