<?php

namespace AppBundle\Transformer;

use AppBundle\Document\Etablissement;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EtablissementsTransformer implements DataTransformerInterface {

    protected $dm;
    protected $societe;

    public function __construct(DocumentManager $dm, $societe) {
        $this->dm = $dm;
        $this->societe = $societe;
    }

    public function transform($values) {
        $result = array();
        $etablissements = $this->dm->getRepository('AppBundle:Etablissement')->findAllOrderedByIdentifiantSociete($this->societe);
        $etablissementsArray = array();
        foreach ($values as $value) {
            $etablissementsArray[] = $value->getId();
        }
        foreach ($etablissements as $index => $etablissement) {
            if (in_array($etablissement->getId(), $etablissements)) {
                $result[] = $index;
            }
        }
        return $result;
    }

    public function reverseTransform($values) {
        $result = array();
        $etablissements = $this->dm->getRepository('AppBundle:Etablissement')->findAllOrderedByIdentifiantSociete($this->societe);
        $etablissementsArray = array();
        foreach ($etablissements as $etablissement) {
            $etablissementsArray[$etablissement->getId()] = $etablissement;
        }
        foreach ($values as $value) {
            $result[] = $etablissementsArray[$value];
        }
        return $result;
    }

}
