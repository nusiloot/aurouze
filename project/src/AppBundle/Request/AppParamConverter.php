<?php

namespace AppBundle\Request;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Model\DocumentEtablissementInterface;
use AppBundle\Model\DocumentSocieteInterface;
use AppBundle\Document\Societe;
use AppBundle\Document\Etablissement;

class AppParamConverter extends DoctrineParamConverter
{

    public function apply(Request $request, ParamConverter $configuration)
    {
        $return = parent::apply($request, $configuration);

        if(!$return) {

            return $return;
        }

        $object = $request->attributes->get($configuration->getName());

        if($object instanceof DocumentEtablissementInterface) {
            $request->attributes->set('etablissement', $object->getEtablissement());
        }

        if($object instanceof DocumentSocieteInterface) {
            $request->attributes->set('societe', $object->getSociete());
        }

        /*if($request->attributes->get('societe') && $request->attributes->get('societe') instanceof Societe && !$request->attributes->get('etablissement')) {
            $request->attributes->set('etablissement', $request->attributes->get('societe')->getEtablissements()->first());
        }*/

        return $return;
    }
}
