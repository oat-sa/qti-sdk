<?php

namespace qtismtest\data\content;

use qtism\data\content\ModalFeedbackRule;
use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;

class ModalFeedbackRuleTest extends QtiSmTestCase
{
    public function testCreateWrongOutcomeIdentifier()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'outcomeIdentifier' argument must be a valid QTI identifier, '999' given."
        );
        $modalFeedbackRule = new ModalFeedbackRule(999, ShowHide::SHOW, 'IDENTIFIER', 'Title');
    }

    public function testCreateWrongShowHide()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'showHide' argument must be a value from the ShowHide enumeration, 'boolean' given."
        );
        $modalFeedbackRule = new ModalFeedbackRule('OUTCOME', true, 'IDENTIFIER', 'Title');
    }

    public function testCreateWrongIdentifier()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'identifier' argument must be a valid QTI identifier, '999' given."
        );
        $modalFeedbackRule = new ModalFeedbackRule('OUTCOME', ShowHide::SHOW, 999, 'Title');
    }

    public function testCreateWrongTitle()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The 'title' argument must be a string, 'boolean' given."
        );
        $modalFeedbackRule = new ModalFeedbackRule('OUTCOME', ShowHide::SHOW, 'IDENTIFIER', false);
    }
}
