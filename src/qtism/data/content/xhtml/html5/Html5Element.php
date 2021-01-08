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
use qtism\data\content\enums\Role;

/**
 * The base Html 5 element.
 */
abstract class Html5Element extends BodyElement
{
    /**
     * The title characteristic represents advisory information for the tag,
     * such as would be appropriate for a tooltip. On a link, this could be the
     * title or a description of the target resource; on an image, it could be
     * the image credit or a description of the image; on a paragraph, it could
     * be a footnote or commentary on the text; on a citation, it could be
     * further information about the source; on interactive content, it could
     * be a label for, or instructions for, use of the element; and so forth.
     * The value is text.
     *
     * @var string
     */
    private $title = '';

    /**
     * The Html5 ARIA role enumeration.
     * Roles are defined and described by their characteristics.
     * Characteristics define the structural function of a role, such as what a
     * role is, concepts behind it, and what instances the role can or must
     * contain.
     *
     * @var int|null
     */
    private $role;

    /**
     * Create a new Html5 element.
     *
     * For the reason why using null instead of default values, see:
     *
     * @see https://stackoverflow.com/questions/45320353/php-7-1-nullable-default-function-parameter#45320694
     *
     * @param string|null $id A QTI identifier.
     * @param string|null $class One or more class names separated by spaces.
     * @param string|null $lang An RFC3066 language.
     * @param string|null $label A label that does not exceed 256 characters.
     * @param string|null $title A title in the sense of Html title attribute
     * @param int|null $role A role taken in the Role constants.
     */
    public function __construct(
        string $id = null,
        string $class = null,
        string $lang = null,
        string $label = null,
        string $title = null,
        int $role = null
    ) {
        parent::__construct($id ?? '', $class ?? '', $lang ?? '', $label ?? '');

        $this->setTitle($title ?? '');
        $this->setRole($role);
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function hasTitle(): bool
    {
        return $this->title !== '';
    }

    /**
     * @param int|null $role One of the Role constants.
     * @throws InvalidArgumentException when $role parameter is not one of Role constants.
     */
    public function setRole($role = null)
    {
        if ($role !== null && !in_array($role, Role::asArray(), true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The "role" argument must be a value from the Role enumeration, "%s" given.',
                    $role
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

    public function hasRole(): bool
    {
        return $this->role !== null;
    }
}
