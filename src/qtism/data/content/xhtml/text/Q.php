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

namespace qtism\data\content\xhtml\text;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\content\SimpleInline;

/**
 * The XHTML q class.
 */
class Q extends SimpleInline
{
    /**
     * The cite attribute.
     *
     * @var string
     * @qtism-bean-property
     */
    private $cite = '';

    /**
     * Create a new Q object.
     *
     * @param string $id A QTI identifier.
     * @param string $class One or more class names separated by spaces.
     * @param string $lang An RFC3066 language.
     * @param string $label A label that does not exceed 256 characters.
     * @param string $cite
     * @throws InvalidArgumentException If any of the arguments above is invalid.
     */
    public function __construct($id = '', $class = '', $lang = '', $label = '', $cite = '')
    {
        parent::__construct($id, $class, $lang, $label);
        $this->setCite($cite);
    }

    /**
     * Get the cite attribute's value.
     *
     * @return string A URI.
     */
    public function getCite(): string
    {
        return $this->cite;
    }

    /**
     * Set the cite attribute's value.
     *
     * @param string $cite
     * @throws InvalidArgumentException If $cite is not a valid URI.
     */
    public function setCite($cite): void
    {
        if ($cite !== '' && !Format::isUri($cite)) {
            $msg = "The 'cite' argument must be a valid URI, '" . $cite . "' given.";
            throw new InvalidArgumentException($msg);
        }

        $this->cite = $cite;
    }

    /**
     * Whether a value is defined for the cite attribute.
     *
     * @return string
     */
    public function hasCite(): string
    {
        return $this->getCite() !== '';
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'q';
    }
}
