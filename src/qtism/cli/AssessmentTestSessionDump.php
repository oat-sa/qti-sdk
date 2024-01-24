<?php

namespace qtism\cli;

use cli\Arguments;
use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\storage\MemoryStream;
use qtism\data\AssessmentTest;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\TemplateVariable;
use qtism\runtime\common\Variable;
use qtism\runtime\storage\binary\LocalQtiBinaryStorage;
use qtism\runtime\storage\common\StorageException;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\SessionManager;

class AssessmentTestSessionDump extends Cli
{

    /**
     * @inheritDoc
     */
    protected function run(): void
    {
        $arguments = $this->getArguments();

        try {
            $compactDoc = new XmlCompactDocument();
            $compactDoc->load($arguments['xml']);

            /** @var AssessmentTest $test */
            $test = $compactDoc->getDocumentComponent();
            $sessionManager = new SessionManager(new FileSystemFileManager());

            $sessionId = 'dump-qtism-session-id';
            $storage = new LocalQtiBinaryStorage($sessionManager, $test);
            $stream = new MemoryStream(base64_decode($arguments['session']));
            $storage->setStream($stream, $sessionId);
            $session = $storage->retrieve($sessionId);

            $this->out("Assessment Test Session Dump");
            $this->out("============================");
            $this->out('');

            $this->out("Test Identifier: " . $session->getAssessmentTest()->getIdentifier());
            $this->out("Test Title: " . $session->getAssessmentTest()->getTitle());
            $this->out("Test Tool Name: " . $session->getAssessmentTest()->getToolName());
            $this->out("Test Tool Version: " . $session->getAssessmentTest()->getToolVersion());
            $this->out('');

            $this->out("Assessment Item Sessions");
            $this->out('------------------------');

            /** @var AssessmentItemSession $itemSession */
            foreach ($session->getAssessmentItemSessionStore()->getAllAssessmentItemSessions() as $itemSession) {
                $this->out("Assessment Item Identifier: " . $itemSession->getAssessmentItem()->getIdentifier());
                $this->out("Assessment Item Title: " . $itemSession->getAssessmentItem()->getTitle());
                $this->out("Assessment Item Label: " . $itemSession->getAssessmentItem()->getLabel());

                /** @var Variable $variable */
                foreach ($itemSession as $variable) {
                    $lastPart = $variable->getIdentifier() . ': ' . $variable->getValue();
                    $firstPart = 'Response Variable';

                    if ($variable instanceof OutcomeVariable) {
                        $firstPart = 'Outcome Variable';
                    } elseif ($variable instanceof TemplateVariable) {
                        $firstPart = 'TemplateVariable';
                    }

                    $this->out("${firstPart} ${lastPart}");
                }

                $this->out('');
            }

        } catch (XmlStorageException $e) {
            $this->fail('XML Compact Test document could not be read.');
        } catch (StorageException $e) {
            $this->fail('The session could not be rebuilt.');
        }

    }

    /**
     * @inheritDoc
     */
    protected function setupArguments(): Arguments
    {
        $arguments = new Arguments(['strict' => false]);

        // -- Options
        // Session option.
        $arguments->addOption(
            ['session'],
            [
                'description' => 'The base64 session string to dump.',
            ]
        );

        // XML option.
        $arguments->addOption(
            ['xml'],
            [
                'description' => 'The path to the related XML Compact Test file.',
            ]
        );

        return $arguments;
    }

    /**
     * @inheritDoc
     */
    protected function checkArguments(): void
    {
        $arguments = $this->getArguments();

        if ($arguments['xml'] === null) {
            $this->fail('Please provide the --xml argument.');
        } else if ($arguments['session'] === null) {
            $this->fail('Please provide the --session argument.');
        }
    }
}