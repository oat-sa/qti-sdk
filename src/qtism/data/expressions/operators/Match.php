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
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\expressions\operators;

/**
 * This class provides backward compatibility Match operator in PHP 7.*.
 * In PHP 8.*, match is a reserved word, the Match operator class has been
 * renamed MacthOperator but compact tests contain generated PHP code which
 * contains references to the Match class. This class makes sure these compact
 * tests still run in PHP 7.*. When run on PHP 8.0, the compact tests have to
 * be updated either by re-publishing the test or by running a script to update
 * the generated PHP code.
 */
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    class Match extends MatchOperator
    {
    }
}
