<?php

namespace AppBundle\Transformer;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use AppBundle\Document\CompteTag;
use AppBundle\Manager\CompteManager;

class TagsTransformer implements DataTransformerInterface {

    protected $dm;
    protected $compteId;

    public function __construct(DocumentManager $dm, $compteId) {
        $this->compteId = $compteId;
        $this->dm = $dm;
    }

    public function transform($values) {
        $result = array();

        $tags = $tags = $this->dm->getRepository('AppBundle:Compte')->findOneById($this->compteId)->getTagsArray();
        $tagsArray = array();
        foreach ($values as $value) {
            $tagsArray[] = $value->getIdentifiant();
        }
        foreach ($tags as $index => $tag) {
            if (in_array($tag->getIdentifiant(), $tagsArray)) {
                $result[] = $index;
            }
        }
        return $result;
    }

    public function reverseTransform($values) {
        $result = array();
        $tags = $this->dm->getRepository('AppBundle:Compte')->findOneById($this->compteId)->getTagsArray();
        $tagsPreCal = CompteManager::$tagsCompteLibelles;
        foreach ($values as $value) {

            if (!array_key_exists($value, $tags)) {
                $tag = new CompteTag();              
                $tag->setNom($value);
                if (array_key_exists($value, $tagsPreCal)) {
                    $tag->setNom($tagsPreCal[$value]);
                }
                $this->dm->getRepository('AppBundle:Compte')->findOneById($this->compteId)->addTag($tag);
                $result[] = $tag;
            } else {
                $result[] = $tags[$value];
            }
        }
        return $result;
    }

}
