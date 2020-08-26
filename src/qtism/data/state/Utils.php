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

namespace qtism\data\state;

use qtism\common\collections\IdentifierCollection;
use qtism\data\content\interactions\Interaction;

/**
 * A class providing State utility methods.
 */
class Utils
{
    /**
     * Create a Shuffling component from a given Interaction object.
     *
     * A Shuffling object will be created depending on the $interaction object given.
     * If $interaction is an interaction type subject to shuffling e.g. choiceInteraction,
     * orderInteraction, associateInteraction, matchInteraction, gapMatchInteraction,
     * inlineChoiceInteraction, a Shuffling object is returned.
     *
     * Otherwise, the method returns false to indicate that no Shuffling component
     * can be built from the given $interaction object.
     *
     * @param Interaction $interaction
     * @return Shuffling|bool
     */
    public static function createShufflingFromInteraction(Interaction $interaction)
    {
        $className = $interaction->getQtiClassName();
        $groups = [];
        $shufflableInteractions = [
            'choiceInteraction',
            'orderInteraction',
            'associateInteraction',
            'matchInteraction',
            'gapMatchInteraction',
            'inlineChoiceInteraction',
        ];

        $returnValue = false;

        if (in_array($className, $shufflableInteractions) && $interaction->mustShuffle() === true) {
            if ($className === 'choiceInteraction' || $className === 'orderInteraction') {
                $choices = $interaction->getComponentsByClassName('simpleChoice');
                $groups[] = ['identifiers' => [], 'fixed' => []];
                foreach ($choices as $choice) {
                    $groups[0]['identifiers'][] = $choice->getIdentifier();
                    if ($choice->isFixed() === true) {
                        $groups[0]['fixed'][] = $choice->getIdentifier();
                    }
                }
            } elseif ($className === 'associateInteraction') {
                $choices = $interaction->getComponentsByClassName('simpleAssociableChoice');
                $groups[] = ['identifiers' => [], 'fixed' => []];
                foreach ($choices as $choice) {
                    $groups[0]['identifiers'][] = $choice->getIdentifier();
                    if ($choice->isFixed() === true) {
                        $groups[0]['fixed'][] = $choice->getIdentifier();
                    }
                }
            } elseif ($className === 'matchInteraction') {
                $matchSets = $interaction->getComponentsByClassName('simpleMatchSet');
                $groups[] = ['identifiers' => [], 'fixed' => []];
                $groups[] = ['identifiers' => [], 'fixed' => []];
                for ($i = 0; $i < count($matchSets); $i++) {
                    foreach ($matchSets[$i]->getComponentsByClassName('simpleAssociableChoice') as $choice) {
                        $groups[$i]['identifiers'][] = $choice->getIdentifier();
                        if ($choice->isFixed() === true) {
                            $groups[0]['fixed'][] = $choice->getIdentifier();
                        }
                    }
                }
            } elseif ($className === 'gapMatchInteraction') {
                $choices = $interaction->getComponentsByClassName(['gapText', 'gapImg']);
                $groups[] = ['identifiers' => [], 'fixed' => []];
                foreach ($choices as $choice) {
                    $groups[0]['identifiers'][] = $choice->getIdentifier();
                    if ($choice->isFixed() === true) {
                        $groups[0]['fixed'][] = $choice->getIdentifier();
                    }
                }
            } elseif ($className === 'inlineChoiceInteraction') {
                $choices = $interaction->getComponentsByClassName('inlineChoice');
                $groups[] = ['identifiers' => [], 'fixed' => []];
                foreach ($choices as $choice) {
                    $groups[0]['identifiers'][] = $choice->getIdentifier();
                    if ($choice->isFixed() === true) {
                        $groups[0]['fixed'][] = $choice->getIdentifier();
                    }
                }
            }

            $responseIdentifier = $interaction->getResponseIdentifier();
            $shufflingGroups = new ShufflingGroupCollection();

            foreach ($groups as $group) {
                $shufflingGroup = new ShufflingGroup(new IdentifierCollection($group['identifiers']));
                $shufflingGroup->setFixedIdentifiers(new IdentifierCollection($group['fixed']));
                $shufflingGroups[] = $shufflingGroup;
            }

            $returnValue = new Shuffling($responseIdentifier, $shufflingGroups);
        }

        return $returnValue;
    }
}
