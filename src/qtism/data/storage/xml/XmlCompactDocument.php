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
 * @author Julien Sébire <julien@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml;

use Exception;
use qtism\data\AssessmentItem;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionRef;
use qtism\data\AssessmentTest;
use qtism\data\content\RubricBlockRef;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\ExtendedAssessmentSection;
use qtism\data\ExtendedAssessmentTest;
use qtism\data\ExtendedTestPart;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentIterator;
use qtism\data\storage\FileResolver;
use qtism\data\storage\LocalFileResolver;
use qtism\data\storage\xml\versions\CompactVersion;
use qtism\data\storage\xml\versions\QtiVersionException;
use qtism\data\TestFeedbackRef;
use qtism\data\TestPart;
use ReflectionException;
use SplObjectStorage;

/**
 * The XmlCompactDocument class represents a test and the needed information from its items to be runnable.
 *
 * Compacting a test within items information to represent a test as a single unit. This class references
 * the minimal data required to make a test runnable. By runnable, we mean a test that can be instantiated,
 * to represent the item flow that will be presented to the candidate. In other words, the intrinsic content
 * of the test in addition with item's:
 *
 * * Response Processing
 * * Variable Declarations
 * * Modal feedback conditions
 */
class XmlCompactDocument extends XmlDocument
{
    /**
     * Whether or not the rubricBlock elements
     * must be separated from the core document.
     *
     * @var bool
     */
    private $explodeRubricBlocks = false;

    /**
     * Whether or not the testFeedback elements
     * must be separated from the core document.
     *
     * @var bool
     */
    private $explodeTestFeedbacks = false;

    /**
     * XmlCompactDocument constructor.
     *
     * Create a new XmlCompactDocument object.
     * Kept for BC reason.
     *
     * @param string $version
     * @param QtiComponent|null $documentComponent
     */
    public function __construct($version = '2.1.0', QtiComponent $documentComponent = null)
    {
        // Version 1.0 was used in legacy code, let's keep it BC.
        if ($version === '1.0') {
            $version = '2.1.0';
        }

        parent::__construct($version, $documentComponent);
    }

    /**
     * Sets version to a supported QTI Compact version.
     *
     * @param string $versionNumber A QTI Compact version number e.g. '2.1.0'.
     * @throws QtiVersionException when version is not supported for QTI Compact.
     */
    public function setVersion(string $versionNumber)
    {
        $this->version = CompactVersion::create($versionNumber);
    }

    /**
     * Whether or not the rubrickBlock components contained in the document
     * should be separated from the document.
     *
     * If $explodedRubricBlocks is set to true, a call to
     * XmlCompactDocument::save() will make the following rules to be applied:
     *
     * * rubricBlock components will be removed from the document.
     * * a replacement of the rubricBlock components by rubricBlockRef components with a suitable value for identifier and href attributes will occur.
     * * place the substituted rubricBlock content in separate QTI-XML files, in a valid location and with a valid name regarding the generated rubricBlockRef components.
     *
     * Please note that this is taken under consideration only when the XmlDocument::save() method is used.
     *
     * @param bool $explodeRubricBlocks Whether rubrickBlock components must be exploded into multiple documents and replaced by rubricBlockRef components.
     */
    public function setExplodeRubricBlocks($explodeRubricBlocks)
    {
        $this->explodeRubricBlocks = $explodeRubricBlocks;
    }

    /**
     * Whether or not the rubricBlock components contained in the document should be separated from the document.
     *
     * @return bool
     */
    public function mustExplodeRubricBlocks()
    {
        return $this->explodeRubricBlocks;
    }

    /**
     * Whether or not the testFeedback components contained in the document should be separated from the document.
     *
     * If $explodeTestFeedbacs is set to true, a call to XmlCompactDocument::save() will make the following rules to be applied:
     *
     * * testFeedback elements will be removed from the document.
     * * a replacement of the testFeedback components by testFeedbackRef components with a suitable value for the href attribute will occur.
     * * place the substituted testFeedback contents in separate QTI-XML files, in a valid location and with a valid name regarding the generated testFeedbackRef components.
     *
     * @param bool $explodeTestFeedbacks
     */
    public function setExplodeTestFeedbacks($explodeTestFeedbacks)
    {
        $this->explodeTestFeedbacks = $explodeTestFeedbacks;
    }

    /**
     * Whether or not the testFeedback components contained in the document should be separated from the document.
     *
     * @return bool
     */
    public function mustExplodeTestFeedbacks()
    {
        return $this->explodeTestFeedbacks;
    }

    /**
     * Create a new instance of XmlCompactDocument from an XmlAssessmentTestDocument.
     *
     * @param XmlDocument $xmlAssessmentTestDocument An XmlAssessmentTestDocument object you want to store as a compact XML file.
     * @param FileResolver $resolver (optional) A resolver aiming at resolving assessmentSectionRef and assessmentItemRef components.
     * @param string $version QTI version to compile to.
     * @return XmlCompactDocument An XmlCompactAssessmentTestDocument object.
     * @throws XmlStorageException If an error occurs while transforming the XmlAssessmentTestDocument object into an XmlCompactAssessmentTestDocument object.
     * @throws ReflectionException
     */
    public static function createFromXmlAssessmentTestDocument(XmlDocument $xmlAssessmentTestDocument, FileResolver $resolver = null, $version = '2.1')
    {
        $compactAssessmentTest = new XmlCompactDocument($version);
        $compactAssessmentTest->setFilesystem($xmlAssessmentTestDocument->getFilesystem());

        $identifier = $xmlAssessmentTestDocument->getDocumentComponent()->getIdentifier();
        $title = $xmlAssessmentTestDocument->getDocumentComponent()->getTitle();

        $assessmentTest = new AssessmentTest($identifier, $title);
        $assessmentTest->setOutcomeDeclarations($xmlAssessmentTestDocument->getDocumentComponent()->getOutcomeDeclarations());
        $assessmentTest->setOutcomeProcessing($xmlAssessmentTestDocument->getDocumentComponent()->getOutcomeProcessing());
        $assessmentTest->setTestFeedbacks($xmlAssessmentTestDocument->getDocumentComponent()->getTestFeedbacks());
        $assessmentTest->setTestParts($xmlAssessmentTestDocument->getDocumentComponent()->getTestParts());
        $assessmentTest->setTimeLimits($xmlAssessmentTestDocument->getDocumentComponent()->getTimeLimits());
        $assessmentTest->setToolName($xmlAssessmentTestDocument->getDocumentComponent()->getToolName());
        $assessmentTest->setToolVersion($xmlAssessmentTestDocument->getDocumentComponent()->getToolVersion());

        if ($resolver === null) {
            $resolver = new LocalFileResolver($xmlAssessmentTestDocument->getUrl());
        } else {
            $resolver->setBasePath($xmlAssessmentTestDocument->getUrl());
        }

        // It simply consists of replacing assessmentItemRef and assessmentSectionRef elements.
        $trail = []; // trailEntry[0] = a component, trailEntry[1] = from where we are coming (parent).
        $mark = [];
        $root = $xmlAssessmentTestDocument->getDocumentComponent();

        // Stores the resolved assessmentSection <-> XmlDocument documents during the compaction process.
        $sectionStore = new SplObjectStorage();

        array_push($trail, [$root, $root]);

        while (count($trail) > 0) {
            $trailer = array_pop($trail);
            $component = $trailer[0];
            $previous = $trailer[1];

            if (!in_array($component, $mark) && count($component->getComponents()) > 0) {
                // First pass on a hierarchical node... go deeper in the n-ary tree.
                array_push($mark, $component);

                // We want to go back on this component.
                array_push($trail, $trailer);

                // Prepare further exploration.
                foreach ($component->getComponents()->getArrayCopy() as $comp) {
                    array_push($trail, [$comp, $component]);
                }
            } elseif (in_array($component, $mark) || count($component->getComponents()) === 0) {
                // Second pass on a hierarchical node (we are bubbling up accross the n-ary tree)
                // OR
                // Leaf node
                if ($component instanceof AssessmentItemRef) {
                    // Transform the ref in an compact extended ref.
                    $compactRef = ExtendedAssessmentItemRef::createFromAssessmentItemRef($component);
                    // find the old one and replace it.
                    $previousParts = $previous->getSectionParts();
                    foreach ($previousParts as $k => $previousPart) {
                        if ($previousParts[$k] === $component) {
                            // If the previous processed component is an XmlAssessmentSectionDocument,
                            // it means that the given baseUri must be adapted.
                            $baseUri = $xmlAssessmentTestDocument->getUrl();
                            if ($previous instanceof AssessmentSection && isset($sectionStore[$previous])) {
                                $baseUri = $sectionStore[$previous]->getUrl();
                            }

                            $resolver->setBasePath($baseUri);
                            self::resolveAssessmentItemRef($xmlAssessmentTestDocument, $compactRef, $resolver);

                            $previousParts->replace($component, $compactRef);
                            break;
                        }
                    }
                } elseif ($component instanceof AssessmentSectionRef) {
                    // We follow the unreferenced AssessmentSection as if it was
                    // the 1st pass.
                    $baseUri = $xmlAssessmentTestDocument->getUrl();
                    if ($previous instanceof AssessmentSection && isset($sectionStore[$previous])) {
                        $baseUri = $sectionStore[$previous]->getUrl();
                    }

                    $resolver->setBasePath($baseUri);

                    $assessmentSectionDocument = self::resolveAssessmentSectionRef($xmlAssessmentTestDocument, $component, $resolver);
                    $assessmentSection = $assessmentSectionDocument->getDocumentComponent();
                    $sectionStore[$assessmentSection] = $assessmentSectionDocument;

                    $previousParts = $previous->getSectionParts();
                    foreach ($previousParts as $k => $previousPart) {
                        if ($previousParts[$k] === $component) {
                            $previousParts->replace($component, $assessmentSection);
                            break;
                        }
                    }

                    array_push($trail, [$assessmentSection, $previous]);
                } elseif ($component instanceof AssessmentSection) {
                    $assessmentSection = ExtendedAssessmentSection::createFromAssessmentSection($component);

                    $previousParts = ($previous instanceof TestPart) ? $previous->getAssessmentSections() : $previous->getSectionParts();
                    foreach ($previousParts as $k => $previousPart) {
                        if ($previousParts[$k] === $component) {
                            $previousParts->replace($component, $assessmentSection);
                            break;
                        }
                    }
                } elseif ($component instanceof TestPart) {
                    $testPart = ExtendedTestPart::createFromTestPart($component);
                    $root->getTestParts()->replace($component, $testPart);
                } elseif ($component === $root) {
                    // 2nd pass on the root, we have to stop.
                    $compactAssessmentTest->setDocumentComponent(ExtendedAssessmentTest::createFromAssessmentTest($assessmentTest));

                    return $compactAssessmentTest;
                }
            }
        }
    }

    /**
     * Dereference the file referenced by an assessmentItemRef and add
     * outcome/responseDeclarations to the compact one.
     *
     * @param XmlDocument $sourceDocument The source document from where assessmentItemRef must be resolved.
     * @param ExtendedAssessmentItemRef $compactAssessmentItemRef A previously instantiated ExtendedAssessmentItemRef object.
     * @param FileResolver $resolver The Resolver to be used to resolver AssessmentItemRef's href attribute.
     * @throws XmlStorageException If an error occurs (e.g. file not found at URI or unmarshalling issue) during the dereferencing.
     */
    protected static function resolveAssessmentItemRef(XmlDocument $sourceDocument, ExtendedAssessmentItemRef $compactAssessmentItemRef, FileResolver $resolver)
    {
        try {
            $href = $resolver->resolve($compactAssessmentItemRef->getHref());

            $doc = new XmlDocument();
            $doc->setFilesystem($sourceDocument->getFilesystem());
            $doc->load($href);

            // Resolve external documents.
            $doc->xInclude();
            $doc->resolveTemplateLocation();

            /** @var AssessmentItem $item */
            $item = $doc->getDocumentComponent();

            foreach ($item->getResponseDeclarations() as $resp) {
                $compactAssessmentItemRef->addResponseDeclaration($resp);
            }

            foreach ($item->getOutcomeDeclarations() as $out) {
                $compactAssessmentItemRef->addOutcomeDeclaration($out);
            }

            foreach ($item->getTemplateDeclarations() as $tpl) {
                $compactAssessmentItemRef->addTemplateDeclaration($tpl);
            }

            foreach ($item->getModalFeedbackRules() as $modalFeedbackRule) {
                $compactAssessmentItemRef->addModalFeedbackRule($modalFeedbackRule);
            }

            if ($item->hasResponseProcessing() === true) {
                $compactAssessmentItemRef->setResponseProcessing($item->getResponseProcessing());
            }

            if ($item->hasTemplateProcessing() === true) {
                $compactAssessmentItemRef->setTemplateProcessing($item->getTemplateProcessing());
            }

            $compactAssessmentItemRef->setShufflings($item->getShufflings());
            $compactAssessmentItemRef->setAdaptive($item->isAdaptive());
            $compactAssessmentItemRef->setTimeDependent($item->isTimeDependent());
            $compactAssessmentItemRef->setEndAttemptIdentifiers($item->getEndAttemptIdentifiers());
            $compactAssessmentItemRef->setResponseValidityConstraints($item->getResponseValidityConstraints());
            $compactAssessmentItemRef->setTitle($item->getTitle());
            $compactAssessmentItemRef->setLabel($item->getLabel());
        } catch (Exception $e) {
            $msg = "An error occurred while unreferencing item reference with identifier '" . $compactAssessmentItemRef->getIdentifier() . "'.";
            throw new XmlStorageException($msg, XmlStorageException::RESOLUTION, $e);
        }
    }

    /**
     * Dereference the file referenced by an assessmentSectionRef.
     *
     * The xinclude elements in the target assessmentSection file will be resolved at the same time.
     *
     * @param XmlDocument $sourceDocument The source document from where assessmentItemRef must be resolved.
     * @param AssessmentSectionRef $assessmentSectionRef An AssessmentSectionRef object to dereference.
     * @param FileResolver $resolver The Resolver object to be used to resolve AssessmentSectionRef's href attribute.
     * @return XmlDocument The AssessmentSection referenced by $assessmentSectionRef as an XmlDocument object.
     * @throws XmlStorageException If an error occurs while dereferencing the referenced file.
     * @throws ReflectionException
     */
    protected static function resolveAssessmentSectionRef(XmlDocument $sourceDocument, AssessmentSectionRef $assessmentSectionRef, FileResolver $resolver)
    {
        try {
            $href = $resolver->resolve($assessmentSectionRef->getHref());

            $doc = new XmlDocument();
            $doc->setFilesystem($sourceDocument->getFilesystem());
            $doc->load($href);
            $doc->xInclude();

            return $doc;
        } catch (XmlStorageException $e) {
            $msg = "An error occurred while unreferencing section reference with identifier '" . $assessmentSectionRef->getIdentifier() . "'.";
            throw new XmlStorageException($msg, XmlStorageException::RESOLUTION, $e);
        }
    }

    /**
     * @param QtiComponent $documentComponent
     * @param string $uri
     * @throws XmlStorageException
     * @throws marshalling\MarshallingException
     */
    public function beforeSave(QtiComponent $documentComponent, $uri)
    {
        // Take care of rubricBlock explosion. Transform actual rubricBlocks in rubricBlockRefs.
        if ($this->mustExplodeRubricBlocks() === true) {
            foreach ($this->explodeRubricBlocks() as $href => $rubricBlock) {
                try {
                    $doc = new XmlDocument();
                    $doc->setFilesystem($this->getFilesystem());
                    $doc->setDocumentComponent($rubricBlock);

                    $pathinfo = pathinfo($uri);
                    $doc->save($pathinfo['dirname'] . DIRECTORY_SEPARATOR . $href);
                } catch (XmlStorageException $e) {
                    $msg = 'An error occurred while creating external rubrickBlock definition(s).';
                    throw new XmlStorageException($msg, XmlStorageException::UNKNOWN, $e);
                }
            }
        }

        // Take care of testFeedback explosion. Transform actual testFeedbacks in testFeedbackRefs.
        if ($this->mustExplodeTestFeedbacks() === true) {
            $iterator = new QtiComponentIterator($documentComponent, ['testFeedback']);
            $testPartCount = new SplObjectStorage();
            $testCount = 0;

            foreach ($iterator as $testFeedback) {
                $parent = $iterator->parent();

                if ($parent instanceof TestPart) {
                    if (isset($testPartCount[$parent]) === false) {
                        $testPartCount[$parent] = 0;
                    }

                    $testPartCount[$parent] += 1;
                    $occurence = $testPartCount[$parent];
                } else {
                    // It's a testFeedback related to an assessmentTest.
                    $testCount++;
                    $occurence = $testCount;
                }

                $parentId = $parent->getIdentifier();
                $href = "./testFeedback_TF_${parentId}_${occurence}.xml";

                // Generate the document.
                $doc = new XmlDocument();
                $doc->setFilesystem($this->getFilesystem());
                $doc->setDocumentComponent($testFeedback);

                try {
                    $pathinfo = pathinfo($uri);
                    $doc->save($pathinfo['dirname'] . DIRECTORY_SEPARATOR . $href);

                    $parent->getTestFeedbacks()->remove($testFeedback);
                    $testFeedbackRefs = $parent->getTestFeedbackRefs();
                    $testFeedbackRefs[] = new TestFeedbackRef(
                        $testFeedback->getIdentifier(),
                        $testFeedback->getOutcomeIdentifier(),
                        $testFeedback->getAccess(),
                        $testFeedback->getShowHide(),
                        $href
                    );
                } catch (XmlStorageException $e) {
                    $msg = 'An error occurred while creating external testFeedback definition(s).';
                    throw new XmlStorageException($msg, XmlStorageException::UNKNOWN, $e);
                }
            }
        }
    }

    /**
     * Explode Rubric Blocks into RubricBlockRefs.
     *
     * @return array where keys are RubricBlockRefs href and values are RubricBlocs as QtiComponent objets.
     */
    public function explodeRubricBlocks()
    {
        // Get all rubricBlock elements...
        $iterator = new QtiComponentIterator($this->getDocumentComponent(), ['rubricBlock']);
        $sectionCount = new SplObjectStorage();
        $references = [];

        foreach ($iterator as $rubricBlock) {
            // $section contains the assessmentSection the rubricBlock is related to.
            $section = $iterator->parent();

            // determine the occurence number of the rubricBlock relative to its section.
            if (isset($sectionCount[$section]) === false) {
                $sectionCount[$section] = 0;
            }

            $sectionCount[$section] += 1;
            $occurence = $sectionCount[$section];

            // determine a suitable file name for the external rubricBlock definition.
            $rubricBlockRefId = 'RB_' . $section->getIdentifier() . '_' . $occurence;
            $href = './rubricBlock_' . $rubricBlockRefId . '.xml';

            // replace the rubric block with a reference.
            $sectionRubricBlocks = $section->getRubricBlocks();
            $sectionRubricBlocks->remove($rubricBlock);

            $sectionRubricBlockRefs = $section->getRubricBlockRefs();
            $sectionRubricBlockRefs[] = new RubricBlockRef($rubricBlockRefId, $href);

            $references[$href] = $rubricBlock;
        }

        return $references;
    }

    /**
     * Infer the QTI Compact version of the document from its XML definition.
     *
     * @return string a semantic version inferred from the document.
     * @throws XmlStorageException when the version can not be inferred.
     */
    protected function inferVersion(): string
    {
        return CompactVersion::infer($this->getDomDocument());
    }
}
