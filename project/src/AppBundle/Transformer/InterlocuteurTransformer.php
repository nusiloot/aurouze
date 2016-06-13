<?php

namespace AppBundle\Transformer;

use AppBundle\Document\Etablissement;
use AppBundle\Manager\InterlocuteurManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class InterlocuteurTransformer implements DataTransformerInterface {

    protected $im;

    public function __construct(InterlocuteurManager $im) {
        $this->im = $im;
    }

    public function transform($issue) {
        if (null === $issue) {
            return '';
        }

        return $issue->getId();
    }

    public function reverseTransform($issueNumber) {
        // no issue number? It's optional, so that's ok
        if (!$issueNumber) {
            return;
        }

        $issue = $this->im->find($issueNumber);

        if (null === $issue) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An issue with number "%s" does not exist!',
                $issueNumber
            ));
        }

        return $issue;
    }

}
