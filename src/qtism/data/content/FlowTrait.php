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

namespace qtism\data\content;

use InvalidArgumentException;
use qtism\common\utils\Format;

/**
 * The Flow trait.
 *
 * This trait deals with xml:base information of a QTI content Component.
 *
 * @authorJérôme Bogaerts <jerome@taotesting.com>
 * @see \qtism\data\content\Flow
 */
trait FlowTrait
{
    /**
     * xml:base value.
     *
     * The current value of xml:base.
     *
     * @var string
     */
    private $xmlBase = '';

    /**
     * setXmlBase method implementation.
     *
     * @see \qtism\data\content\Flow::setXmlBase()
     */
    public function setXmlBase($xmlBase = '')
    {
        if (is_string($xmlBase) && (empty($xmlBase) || Format::isUri($xmlBase))) {
            $this->xmlBase = $xmlBase;
        } else {
            $msg = "The 'xmlBase' argument must be an empty string or a valid URI, '" . $xmlBase . "' given";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * getXmlBase method implementation.
     *
     * @see \qtism\data\content\Flow::getXmlBase()
     */
    public function getXmlBase()
    {
        return $this->xmlBase;
    }

    /**
     * hasXmlBase method implementation.
     *
     * @see \qtism\data\content\Flow::hasXmlBase()
     */
    public function hasXmlBase()
    {
        return $this->getXmlBase() !== '';
    }
}
