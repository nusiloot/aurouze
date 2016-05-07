<?php

namespace AppBundle\Transformer;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use AppBundle\Document\CompteTag;
use AppBundle\Document\Compte;
use AppBundle\Manager\CompteManager;

class TagsTransformer implements DataTransformerInterface {

    protected $dm;
    protected $compte;

    public function __construct(DocumentManager $dm, Compte $compte) {
        $this->compte = $compte;
        $this->dm = $dm;
    }

    public function transform($values) {
        $result = array();
        $tags = array();
        if ($this->compte) {
            $tags = $this->compte->getTagsArray();
        }
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
        $tags = array();
        if ($this->compte) {
            $tags = $this->compte->getTagsArray();
        }
        $tagsPreCal = CompteManager::$tagsCompteLibelles;
        foreach ($values as $value) {

            if (!array_key_exists($value, $tags)) {
                $tag = new CompteTag();
                $tag->setNom($value);
                if (array_key_exists($value, $tagsPreCal)) {
                    $tag->setNom($tagsPreCal[$value]);
                }
                $this->compte->addTag($tag);
                $result[] = $tag;
            } else {
                $result[] = $tags[$value];
            }
        }
        return $result;
    }

}
