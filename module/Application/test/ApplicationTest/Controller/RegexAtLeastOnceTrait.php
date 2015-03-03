<?php

namespace ApplicationTest\Controller;

use Zend\Dom\Document;
use PHPUnit_Framework_ExpectationFailedException;

trait RegexAtLeastOnceTrait
{
    private function assertQueryContentRegexAtLeastOnce($path, $pattern, $useXpath = false, $content = null)
    {
        if (!$content) {
            $response = $this->getResponse();
            $content = $response->getContent();
        }

        $document = new Document($content);

        if ($useXpath) {
            $document->registerXpathNamespaces($this->xpathNamespaces);
        }

        $result   = Document\Query::execute($path, $document, $useXpath ? Document\Query::TYPE_XPATH : Document\Query::TYPE_CSS);

        if ($result->count() == 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            ));
        }

        foreach ($result as $node) {
            if (preg_match($pattern, $node->nodeValue))
                return;
        }

        throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
            'Failed asserting node denoted by %s CONTAINS content MATCHING "%s"',
            $path,
            $pattern
        ));
    }
}
