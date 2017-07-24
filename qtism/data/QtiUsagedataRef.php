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
 *
 * @author Aleksej Tikhanovich <aleksej@taotesting.com>
 *
 */
class QtiUsagedataRef extends SectionPart
{
    /**
     * A URI to refer the item's file.
     *
     * @var string
     * @qtism-bean-property
     */
    private $href;


    /**
     * Create a new instance of QtiUsagedataRef.
     *
     * @param string $identifier A QTI Identifier.
     * @param string $href The URI to refer to the item's file.
     * @throws InvalidArgumentException If $href is not a string.
     */
    public function __construct($identifier, $href)
    {
        parent::__construct($identifier);
        $this->setHref($href);
    }

    /**
     * Get the URI that references the item's file.
     *
     * @return string A URI.
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Set the URI that references the item's file.
     *
     * @param string $href A URI.
     * @throws InvalidArgumentException If $href is not a string.
     */
    public function setHref($href)
    {
        if (gettype($href) === 'string') {
            $this->href = $href;
        } else {
            $msg = "href must be a string, '" . gettype($href) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * @return string
     */
    public function getQtiClassName()
    {
        return 'qtiUsagedataRef';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents()
    {
        $comp = array_merge(parent::getComponents()->getArrayCopy());

        return new QtiComponentCollection($comp);
    }
}
