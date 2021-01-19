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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\xhtml\html5;

/**
 * Html 5 media source class to allows authors to specify multiple alternative
 * media resources for media elements.
 */
class Source extends Html5EmptyElement
{
    /**
     * The 'src' characteristic gives the address of the media resource.
     * It must be a valid non-empty URL potentially surrounded by spaces.
     * This characteristic must be present.
     *
     * @var string
     * @qtism-bean-property
     */
    private $src;

    /**
     * The 'type' characteristic gives the type of the media resource, to help
     * the user agent determine if it can play this media resource before
     * fetching it. If specified, its value must be a valid MIME type.
     *
     * @var string
     * @qtism-bean-property
     */
    private $type = '';

    /**
     * Create a new Source object.
     *
     * @param mixed $src A URI.
     * @param mixed $type The type of the source file.
     * @param mixed $title A title in the sense of Html title attribute
     * @param mixed $role A role taken in the Role constants.
     * @param mixed $id A QTI identifier.
     * @param mixed $class One or more class names separated by spaces.
     * @param mixed $lang An RFC3066 language.
     * @param mixed $label A label that does not exceed 256 characters.
     */
    public function __construct(
        $src,
        $type = null,
        $title = null,
        $role = null,
        $id = null,
        $class = null,
        $lang = null,
        $label = null
    ) {
        parent::__construct($title, $role, $id, $class, $lang, $label);
        
        $this->setSrc($src);
        $this->setType($type);
    }

    /**
     * @param mixed $src
     */
    public function setSrc($src): void
    {
        $this->src = $this->acceptUri($src, 'src');
    }

    public function getSrc(): string
    {
        return $this->src;
    }

    public function setType(?string $type): void
    {
        $this->type = $this->acceptMimeTypeOrNull($type, 'type');
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function hasType(): bool
    {
        return $this->type !== '';
    }

    public function getQtiClassName(): string
    {
        return 'source';
    }
}
