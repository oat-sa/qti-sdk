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

use InvalidArgumentException;
use qtism\data\content\BodyElement;

/**
 * The base Html 5 element.
 */
abstract class Html5Element extends BodyElement
{
    /**
     * Title of the Html5 element.
     *
     * @var string
     */
    private $title = '';

    /**
     * Role of the Html5 element.
     *
     * @var int
     */
    private $role;

    /**
     * Create a new Html5 element.
     *
     * @param string $id A QTI identifier.
     * @param string $class One or more class names separated by spaces.
     * @param string $lang An RFC3066 language.
     * @param string $label A label that does not exceed 256 characters.
     * @param string $title A title in the sense of Html title attribute
     * @param int|null $role A role taken in the Role constants.
     */
    public function __construct(
        $id = '',
        $class = '',
        $lang = '',
        $label = '',
        $title = '',
        $role = null
    ) {
        parent::__construct($id, $class, $lang, $label);
        if ($title !== '') {
            $this->setTitle($title);
        }
        if ($role !== null) {
            $this->setRole($role);
        }
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        if (!is_string($title)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The "title" argument must be a string, "%s" given.',
                    gettype($title)
                )
            );
        }

        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return bool
     */
    public function hasTitle(): bool
    {
        return $this->title !== '';
    }

    /**
     * Sets the role of the html5 element.
     *
     * @param int $role One of the Role constants.
     */
    public function setRole($role)
    {
        if (!in_array($role, Role::asArray(), true)) {
            $given = is_int($role)
                ? $role
                : gettype($role);

            throw new InvalidArgumentException(
                sprintf(
                    'The "role" argument must be a value from the Role enumeration, "%s" given.',
                    $given
                )
            );
        }

        $this->role = $role;
    }

    /**
     * @return int|null
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return bool
     */
    public function hasRole(): bool
    {
        return $this->role !== null;
    }
}
