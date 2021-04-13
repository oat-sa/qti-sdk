<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\storage\xml\XmlStorageException;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

/**
 * Class AssessmentTestSessionNumberXTest
 */
class AssessmentTestSessionNumberXTest extends QtiSmAssessmentTestSessionTestCase
{
    /**
     * @dataProvider numberXMethodProvider
     * @param string $method
     * @throws XmlStorageException
     */
    public function testNumberXNonRunning($method)
    {
        // Test AssessmentTestSession::numberCorrect, numberIncorrect, numberResponded, numberSelected, numberPresented
        //  with a non running test session.
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/subset/number_x.xml');
        $this::assertEquals(0, $session->$method());
        $this::assertEquals(0, $session->$method('S01'));
        $this::assertEquals(0, $session->$method('S01A'));
        $this::assertEquals(0, $session->$method('S01B'));
        $this::assertEquals(0, $session->$method('S02'));

        // query for an unexisting ID.
        $this::assertEquals(0, $session->$method('S0X'));
    }

    /**
     * @return array
     */
    public function numberXMethodProvider()
    {
        return [
            ['numberCorrect'],
            ['numberIncorrect'],
            ['numberSelected'],
            ['numberPresented'],
            ['numberResponded'],
        ];
    }

    public function testNumberXRunning()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/subset/number_x.xml');
        $session->beginTestSession();

        $this::assertEquals(0, $session->numberCorrect());
        $this::assertEquals(0, $session->numberCorrect('S01'));
        $this::assertEquals(0, $session->numberCorrect('S01A'));
        $this::assertEquals(0, $session->numberCorrect('S01B'));
        $this::assertEquals(0, $session->numberCorrect('S02'));

        $this::assertEquals(0, $session->numberIncorrect());
        $this::assertEquals(0, $session->numberIncorrect('S01'));
        $this::assertEquals(0, $session->numberIncorrect('S01A'));
        $this::assertEquals(0, $session->numberIncorrect('S01B'));
        $this::assertEquals(0, $session->numberIncorrect('S02'));

        $this::assertEquals(0, $session->numberPresented());
        $this::assertEquals(0, $session->numberPresented('S01'));
        $this::assertEquals(0, $session->numberPresented('S01A'));
        $this::assertEquals(0, $session->numberPresented('S01B'));
        $this::assertEquals(0, $session->numberPresented('S02'));

        $this::assertEquals(7, $session->numberSelected());
        $this::assertEquals(6, $session->numberSelected('S01'));
        $this::assertEquals(3, $session->numberSelected('S01A'));
        $this::assertEquals(3, $session->numberSelected('S01B'));
        $this::assertEquals(1, $session->numberSelected('S02'));

        $this::assertEquals(0, $session->numberResponded());
        $this::assertEquals(0, $session->numberResponded('S01'));
        $this::assertEquals(0, $session->numberResponded('S01A'));
        $this::assertEquals(0, $session->numberResponded('S01B'));
        $this::assertEquals(0, $session->numberResponded('S02'));

        // -- Correct answer to Q01.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $session->moveNext();

        $this::assertEquals(1, $session->numberCorrect());
        $this::assertEquals(1, $session->numberCorrect('S01'));
        $this::assertEquals(1, $session->numberCorrect('S01A'));
        $this::assertEquals(0, $session->numberCorrect('S01B'));
        $this::assertEquals(0, $session->numberCorrect('S02'));

        $this::assertEquals(0, $session->numberIncorrect());
        $this::assertEquals(0, $session->numberIncorrect('S01'));
        $this::assertEquals(0, $session->numberIncorrect('S01A'));
        $this::assertEquals(0, $session->numberIncorrect('S01B'));
        $this::assertEquals(0, $session->numberIncorrect('S02'));

        $this::assertEquals(1, $session->numberPresented());
        $this::assertEquals(1, $session->numberPresented('S01'));
        $this::assertEquals(1, $session->numberPresented('S01A'));
        $this::assertEquals(0, $session->numberPresented('S01B'));
        $this::assertEquals(0, $session->numberPresented('S02'));

        $this::assertEquals(7, $session->numberSelected());
        $this::assertEquals(6, $session->numberSelected('S01'));
        $this::assertEquals(3, $session->numberSelected('S01A'));
        $this::assertEquals(3, $session->numberSelected('S01B'));
        $this::assertEquals(1, $session->numberSelected('S02'));

        $this::assertEquals(1, $session->numberResponded());
        $this::assertEquals(1, $session->numberResponded('S01'));
        $this::assertEquals(1, $session->numberResponded('S01A'));
        $this::assertEquals(0, $session->numberResponded('S01B'));
        $this::assertEquals(0, $session->numberResponded('S02'));

        // -- Incorrect answer to Q02.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $session->moveNext();

        $this::assertEquals(1, $session->numberCorrect());
        $this::assertEquals(1, $session->numberCorrect('S01'));
        $this::assertEquals(1, $session->numberCorrect('S01A'));
        $this::assertEquals(0, $session->numberCorrect('S01B'));
        $this::assertEquals(0, $session->numberCorrect('S02'));

        $this::assertEquals(1, $session->numberIncorrect());
        $this::assertEquals(1, $session->numberIncorrect('S01'));
        $this::assertEquals(1, $session->numberIncorrect('S01A'));
        $this::assertEquals(0, $session->numberIncorrect('S01B'));
        $this::assertEquals(0, $session->numberIncorrect('S02'));

        $this::assertEquals(2, $session->numberPresented());
        $this::assertEquals(2, $session->numberPresented('S01'));
        $this::assertEquals(2, $session->numberPresented('S01A'));
        $this::assertEquals(0, $session->numberPresented('S01B'));
        $this::assertEquals(0, $session->numberPresented('S02'));

        $this::assertEquals(7, $session->numberSelected());
        $this::assertEquals(6, $session->numberSelected('S01'));
        $this::assertEquals(3, $session->numberSelected('S01A'));
        $this::assertEquals(3, $session->numberSelected('S01B'));
        $this::assertEquals(1, $session->numberSelected('S02'));

        $this::assertEquals(2, $session->numberResponded());
        $this::assertEquals(2, $session->numberResponded('S01'));
        $this::assertEquals(2, $session->numberResponded('S01A'));
        $this::assertEquals(0, $session->numberResponded('S01B'));
        $this::assertEquals(0, $session->numberResponded('S02'));

        // -- Skip Q03.
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        $this::assertEquals(1, $session->numberCorrect());
        $this::assertEquals(1, $session->numberCorrect('S01'));
        $this::assertEquals(1, $session->numberCorrect('S01A'));
        $this::assertEquals(0, $session->numberCorrect('S01B'));
        $this::assertEquals(0, $session->numberCorrect('S02'));

        $this::assertEquals(2, $session->numberIncorrect());
        $this::assertEquals(2, $session->numberIncorrect('S01'));
        $this::assertEquals(2, $session->numberIncorrect('S01A'));
        $this::assertEquals(0, $session->numberIncorrect('S01B'));
        $this::assertEquals(0, $session->numberIncorrect('S02'));

        $this::assertEquals(3, $session->numberPresented());
        $this::assertEquals(3, $session->numberPresented('S01'));
        $this::assertEquals(3, $session->numberPresented('S01A'));
        $this::assertEquals(0, $session->numberPresented('S01B'));
        $this::assertEquals(0, $session->numberPresented('S02'));

        $this::assertEquals(7, $session->numberSelected());
        $this::assertEquals(6, $session->numberSelected('S01'));
        $this::assertEquals(3, $session->numberSelected('S01A'));
        $this::assertEquals(3, $session->numberSelected('S01B'));
        $this::assertEquals(1, $session->numberSelected('S02'));

        $this::assertEquals(2, $session->numberResponded());
        $this::assertEquals(2, $session->numberResponded('S01'));
        $this::assertEquals(2, $session->numberResponded('S01A'));
        $this::assertEquals(0, $session->numberResponded('S01B'));
        $this::assertEquals(0, $session->numberResponded('S02'));

        // -- Incorrect answer to Q04.1
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceZ'))]));
        $session->moveNext();

        $this::assertEquals(1, $session->numberCorrect());
        $this::assertEquals(1, $session->numberCorrect('S01'));
        $this::assertEquals(1, $session->numberCorrect('S01A'));
        $this::assertEquals(0, $session->numberCorrect('S01B'));
        $this::assertEquals(0, $session->numberCorrect('S02'));

        $this::assertEquals(3, $session->numberIncorrect());
        $this::assertEquals(3, $session->numberIncorrect('S01'));
        $this::assertEquals(2, $session->numberIncorrect('S01A'));
        $this::assertEquals(1, $session->numberIncorrect('S01B'));
        $this::assertEquals(0, $session->numberIncorrect('S02'));

        $this::assertEquals(4, $session->numberPresented());
        $this::assertEquals(4, $session->numberPresented('S01'));
        $this::assertEquals(3, $session->numberPresented('S01A'));
        $this::assertEquals(1, $session->numberPresented('S01B'));
        $this::assertEquals(0, $session->numberPresented('S02'));

        $this::assertEquals(7, $session->numberSelected());
        $this::assertEquals(6, $session->numberSelected('S01'));
        $this::assertEquals(3, $session->numberSelected('S01A'));
        $this::assertEquals(3, $session->numberSelected('S01B'));
        $this::assertEquals(1, $session->numberSelected('S02'));

        $this::assertEquals(3, $session->numberResponded());
        $this::assertEquals(3, $session->numberResponded('S01'));
        $this::assertEquals(2, $session->numberResponded('S01A'));
        $this::assertEquals(1, $session->numberResponded('S01B'));
        $this::assertEquals(0, $session->numberResponded('S02'));

        // -- Correct answer to Q04.2
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceD'))]));
        $session->moveNext();

        $this::assertEquals(2, $session->numberCorrect());
        $this::assertEquals(2, $session->numberCorrect('S01'));
        $this::assertEquals(1, $session->numberCorrect('S01A'));
        $this::assertEquals(1, $session->numberCorrect('S01B'));
        $this::assertEquals(0, $session->numberCorrect('S02'));

        $this::assertEquals(3, $session->numberIncorrect());
        $this::assertEquals(3, $session->numberIncorrect('S01'));
        $this::assertEquals(2, $session->numberIncorrect('S01A'));
        $this::assertEquals(1, $session->numberIncorrect('S01B'));
        $this::assertEquals(0, $session->numberIncorrect('S02'));

        $this::assertEquals(5, $session->numberPresented());
        $this::assertEquals(5, $session->numberPresented('S01'));
        $this::assertEquals(3, $session->numberPresented('S01A'));
        $this::assertEquals(2, $session->numberPresented('S01B'));
        $this::assertEquals(0, $session->numberPresented('S02'));

        $this::assertEquals(7, $session->numberSelected());
        $this::assertEquals(6, $session->numberSelected('S01'));
        $this::assertEquals(3, $session->numberSelected('S01A'));
        $this::assertEquals(3, $session->numberSelected('S01B'));
        $this::assertEquals(1, $session->numberSelected('S02'));

        $this::assertEquals(4, $session->numberResponded());
        $this::assertEquals(4, $session->numberResponded('S01'));
        $this::assertEquals(2, $session->numberResponded('S01A'));
        $this::assertEquals(2, $session->numberResponded('S01B'));
        $this::assertEquals(0, $session->numberResponded('S02'));

        // Skip Q04.3
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        $this::assertEquals(2, $session->numberCorrect());
        $this::assertEquals(2, $session->numberCorrect('S01'));
        $this::assertEquals(1, $session->numberCorrect('S01A'));
        $this::assertEquals(1, $session->numberCorrect('S01B'));
        $this::assertEquals(0, $session->numberCorrect('S02'));

        $this::assertEquals(4, $session->numberIncorrect());
        $this::assertEquals(4, $session->numberIncorrect('S01'));
        $this::assertEquals(2, $session->numberIncorrect('S01A'));
        $this::assertEquals(2, $session->numberIncorrect('S01B'));
        $this::assertEquals(0, $session->numberIncorrect('S02'));

        $this::assertEquals(6, $session->numberPresented());
        $this::assertEquals(6, $session->numberPresented('S01'));
        $this::assertEquals(3, $session->numberPresented('S01A'));
        $this::assertEquals(3, $session->numberPresented('S01B'));
        $this::assertEquals(0, $session->numberPresented('S02'));

        $this::assertEquals(7, $session->numberSelected());
        $this::assertEquals(6, $session->numberSelected('S01'));
        $this::assertEquals(3, $session->numberSelected('S01A'));
        $this::assertEquals(3, $session->numberSelected('S01B'));
        $this::assertEquals(1, $session->numberSelected('S02'));

        $this::assertEquals(4, $session->numberResponded());
        $this::assertEquals(4, $session->numberResponded('S01'));
        $this::assertEquals(2, $session->numberResponded('S01A'));
        $this::assertEquals(2, $session->numberResponded('S01B'));
        $this::assertEquals(0, $session->numberResponded('S02'));

        // Correct answer to Q05.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceE'))]));
        $session->moveNext();

        $this::assertEquals(3, $session->numberCorrect());
        $this::assertEquals(2, $session->numberCorrect('S01'));
        $this::assertEquals(1, $session->numberCorrect('S01A'));
        $this::assertEquals(1, $session->numberCorrect('S01B'));
        $this::assertEquals(1, $session->numberCorrect('S02'));

        $this::assertEquals(4, $session->numberIncorrect());
        $this::assertEquals(4, $session->numberIncorrect('S01'));
        $this::assertEquals(2, $session->numberIncorrect('S01A'));
        $this::assertEquals(2, $session->numberIncorrect('S01B'));
        $this::assertEquals(0, $session->numberIncorrect('S02'));

        $this::assertEquals(7, $session->numberPresented());
        $this::assertEquals(6, $session->numberPresented('S01'));
        $this::assertEquals(3, $session->numberPresented('S01A'));
        $this::assertEquals(3, $session->numberPresented('S01B'));
        $this::assertEquals(1, $session->numberPresented('S02'));

        $this::assertEquals(7, $session->numberSelected());
        $this::assertEquals(6, $session->numberSelected('S01'));
        $this::assertEquals(3, $session->numberSelected('S01A'));
        $this::assertEquals(3, $session->numberSelected('S01B'));
        $this::assertEquals(1, $session->numberSelected('S02'));

        $this::assertEquals(5, $session->numberResponded());
        $this::assertEquals(4, $session->numberResponded('S01'));
        $this::assertEquals(2, $session->numberResponded('S01A'));
        $this::assertEquals(2, $session->numberResponded('S01B'));
        $this::assertEquals(1, $session->numberResponded('S02'));
    }
}
