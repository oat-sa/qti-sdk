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

namespace qtism\data\content\xhtml\html5;

use InvalidArgumentException;
use qtism\common\utils\Format;

/**
 * Html 5 media source class.
 */
class Source extends Html5EmptyElement
{
    /**
     * The required src attribute specifies the URL of the track file.
     * Tracks are formatted in WebVTT format (.vtt files).
     *
     * @var string
     * @qtism-bean-property
     */
    private $src;

    /**
     * Mime type of the source file.
     *
     * @var string
     * @qtism-bean-property
     */
    private $type = '';

    /**
     * Create a new Source object.
     *
     * @param string $src A URI.
     * @param string $type The type of the source file.
     * @param string $id A QTI identifier.
     * @param string $class One or more class names separated by spaces.
     * @param string $lang An RFC3066 language.
     * @param string $label A label that does not exceed 256 characters.
     */
    public function __construct(
        $src,
        $type = null,
        $id = '',
        $class = '',
        $lang = '',
        $label = ''
    ) {
        parent::__construct($id, $class, $lang, $label);
        $this->setSrc($src);
        if ($type !== null) {
            $this->setType($type);
        }
    }

    /**
     * Set the src attribute.
     *
     * @param string $src A URI.
     * @throws InvalidArgumentException If $src is not a valid URI.
     */
    public function setSrc($src)
    {
        if (!Format::isUri($src)) {
            $given = is_string($src)
                ? $src
                : gettype($src);

            throw new InvalidArgumentException(
                sprintf(
                    'The "src" argument must be a valid URI, "%s" given.',
                    $given
                )
            );
        }

        $this->src = $src;
    }

    /**
     * Get the src attribute.
     *
     * @return string A URI.
     */
    public function getSrc(): string
    {
        return $this->src;
    }

    /**
     * Sets the mime type of the source file.
     *
     * @param string $type
     */
    public function setType($type)
    {
        if (!Format::isMimeType($type)) {
            $given = is_string($type)
                ? $type
                : gettype($type);
            throw new InvalidArgumentException(
                sprintf(
                    'The "type" argument must be a valid Mime type, "%s" given.',
                    $given
                )
            );
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function hasType(): bool
    {
        return $this->type !== '';
    }

    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'source';
    }
}
