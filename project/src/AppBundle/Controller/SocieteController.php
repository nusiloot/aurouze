<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Type\SocieteChoiceType;
use AppBundle\Type\SocieteType;
use AppBundle\Type\AttachementType;
use AppBundle\Document\Societe;
use AppBundle\Document\Etablissement;
use AppBundle\Manager\EtablissementManager;
use AppBundle\Document\Attachement;

class SocieteController extends Controller {

    /**
     * @Route("/societe", name="societe")
     */
    public function indexAction(Request $request) {

    	$dm = $this->get('doctrine_mongodb')->getManager();

    	return $this->render('societe/index.html.twig');
    }
    protected static function cmpContacts($a, $b)
    {
    	return ($a['score'] > $b['score']) ? -1 : +1;
    }

    /**
     * @Route("/societe/selection", name="societe_choice")
     */
    public function societeChoiceAction(Request $request) {
    	$formData = $request->get('societe_choice');

    	return $this->redirectToRoute('societe_visualisation', array('id' => $formData['societes']));
    }

    /**
     * @Route("/societe/{id}/visualisation", name="societe_visualisation")
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function visualisationAction(Request $request, $societe) {

    	$dm = $this->get('doctrine_mongodb')->getManager();
      $attachement = new Attachement();
      $uploadAttachementForm = $this->createForm(new AttachementType($dm), $attachement, array(
          'action' => $this->generateUrl('societe_upload_attachement', array('id' => $societe->getId())),
          'method' => 'POST',
      ));

      $uploadModifAttachementForms = array();
      foreach ($societe->getAttachements() as $att) {
        $f = $this->createForm(new AttachementType($dm), $att, array(
            'action' => $this->generateUrl('attachement_modification', array('id' => $att->getId())),
            'method' => 'POST',
        ));
        $uploadModifAttachementForms[$att->getId()] = $f->createView();
      }

      $uploadEtbsAttachementForms = array();
      foreach ($societe->getEtablissements() as $etablissement) {
        $attachement = new Attachement();
        $f = $this->createForm(new AttachementType($dm), $attachement, array(
            'action' => $this->generateUrl('etablissement_upload_attachement', array('id' => $etablissement->getId())),
            'method' => 'POST',
        ));
        $uploadEtbsAttachementForms[$etablissement->getId()] = $f->createView();

        foreach ($etablissement->getAttachements() as $att) {
          $f = $this->createForm(new AttachementType($dm), $att, array(
              'action' => $this->generateUrl('attachement_modification', array('id' => $att->getId())),
              'method' => 'POST',
          ));
          $uploadModifAttachementForms[$att->getId()] = $f->createView();
        }
      }
      $nbContratsSociete = count($this->get('contrat.manager')->getRepository()->findBySociete($societe));

    	return $this->render('societe/visualisation.html.twig', array('societe' => $societe, 'nbContratsSociete' => $nbContratsSociete, 'uploadAttachementForm' => $uploadAttachementForm->createView(), 'uploadEtbsAttachementForms' => $uploadEtbsAttachementForms, 'uploadModifAttachementForms' => $uploadModifAttachementForms));
    }

    /**
     * @Route("/societe/modification/{id}", defaults={"id" = null}, name="societe_modification")
     */
    public function modificationAction(Request $request, $id) {

    	$dm = $this->get('doctrine_mongodb')->getManager();

    	$isNew = ($id)? false : true;
    	$societe = (!$isNew)? $this->get('societe.manager')->getRepository()->find($id) : new Societe();

    	$form = $this->createForm(new SocieteType($this->container, $dm, $isNew), $societe, array(
    			'action' => $this->generateUrl('societe_modification', array('id' => $id)),
    			'method' => 'POST',
    	));
    	$form->handleRequest($request);
    	if ($form->isSubmitted() && $form->isValid()) {
    		$societe = $form->getData();
    		$dm->persist($societe);
    		$dm->flush();
    		if ($isNew && $form->get("generer")->getData()) {
    			 $etablissement = new Etablissement();
    			 $etablissement->setSociete($societe);
    			 $etablissement->setNom($societe->getRaisonSociale());
    			 $etablissement->setType($societe->getType());
    			 $etablissement->setCommentaire($societe->getCommentaire());
    			 $dm->persist($etablissement);
    			 $dm->flush();
    		}
    		return $this->redirectToRoute('societe_visualisation', array('id' => $societe->getId()));
    	}

    	return $this->render('societe/modification.html.twig', array('form' => $form->createView(), 'societe' => $societe,  'isNew' => $isNew));
    }

    /**
     * @Route("/societe/rechercher", name="societe_search")
     */
     public function societeSearchAction(Request $request) {
         $dm = $this->get('doctrine_mongodb')->getManager();
         $response = new Response();
         $societesResult = array();
         $withNonActif = (!$request->get('nonactif'))? false : $request->get('nonactif');
         $this->contructSearchResult($dm->getRepository('AppBundle:Societe')->findByTerms($request->get('term'), $withNonActif),  $societesResult);
         $data = json_encode($societesResult);
         $response->headers->set('Content-Type', 'application/json');
         $response->setContent($data);
         return $response;
     }

     /**
     * @Route("/societe/attachement/{id}/ajout", name="societe_upload_attachement")
     */
     public function attachementUploadAction(Request $request, $id) {
        ini_set ('gd.jpeg_ignore_warning', 1);
        $attachement = new Attachement();
        $dm = $this->get('doctrine_mongodb')->getManager();
        $societe = $this->get('societe.manager')->getRepository()->find($id);
        $uploadAttachementForm = $this->createForm(new AttachementType($dm), $attachement, array(
      			'action' => $this->generateUrl('societe_upload_attachement', array('id' => $id)),
      			'method' => 'POST',
      	));

        if ($request->isMethod('POST')) {
            $uploadAttachementForm->handleRequest($request);
            if($uploadAttachementForm->isValid()){
              $attachement->setSociete($societe);
              $dm->persist($attachement);
              $societe->addAttachement($attachement);
              $dm->flush();
              $attachement->convertBase64AndRemove();
              $dm->flush();
              }

          return $this->redirectToRoute('societe_visualisation', array('id' => $societe->getId()));
        }
    }

    /**
    * @Route("/attachement/{id}/supprimer", name="attachement_delete")
    */
    public function attachementDeleteAction(Request $request, $id) {
       $attachement = $this->get('attachement.manager')->getRepository()->find($id);

       $noremove = $request->get('noremove',false);
       $societe = $attachement->getSociete();
       if(!$societe){
         $societe = $attachement->getEtablissement()->getSociete();
       }
       if(!$societe){
         throw new \Exception('Une erreur s\'est produite : le document '.$attachement->getId().' ne semble être relié à rien!');

       }
       $dm = $this->get('doctrine_mongodb')->getManager();

       try {
           if($noremove){
               $attachement->removeFile();
           }
       } catch (\Symfony\Component\Debug\Exception\ContextErrorException $e) {
         //do nothing
       }

       $dm->remove($attachement);
       $dm->flush();

       return $this->redirectToRoute('societe_visualisation', array('id' => $societe->getId()));
   }

   /**
   * @Route("/attachement/{id}/modification", name="attachement_modification")
   */
   public function attachementModificationAction(Request $request, $id) {
      $attachement = $this->get('attachement.manager')->getRepository()->find($id);
      $lastFile = $attachement->getImageFile();

      $lastName = $attachement->getImageName();

      $societe = $attachement->getSociete();
      if(!$societe){
            $societe = $attachement->getEtablissement()->getSociete();
            $url = $this->generateUrl('etablissement_upload_attachement', array('id' => $attachement->getEtablissement()->getId()));
        }else{
            $url = $this->generateUrl('societe_upload_attachement', array('id' => $societe->getId()));
        }
      if(!$societe){
        throw new \Exception('Une erreur s\'est produite : le document '.$attachement->getId().' ne semble être relié à rien!');

      }
      $dm = $this->get('doctrine_mongodb')->getManager();

      if ($request->isMethod('POST')) {
          $attachementNew = new Attachement();
          $uploadAttachementForm = $this->createForm(new AttachementType($dm), $attachementNew, array(
            'action' => $url,
            'method' => 'POST',
          ));
          $uploadAttachementForm->handleRequest($request);

          if($uploadAttachementForm->isValid()){
              $upData = $uploadAttachementForm->getData();
            if($upData->getImageFile() == null){
                  $attachementNew->setImageName($lastName);
                  $attachementNew->setImageFile($lastFile);
            }
            if($attachement->getSociete()){
              $attachementNew->setSociete($attachement->getSociete());
            }
            if($attachement->getEtablissement()){
              $attachementNew->setEtablissement($attachement->getEtablissement());
            }
            $dm->persist($attachementNew);
            $societe->addAttachement($attachementNew);
            $dm->flush();
          }
          return $this->redirectToRoute('attachement_delete', array('id' => $attachement->getId(),'noremove' => 1));
      }
  }





    public function contructSearchResult($criterias, &$result) {

        foreach ($criterias as $id => $nom) {
            $newResult = new \stdClass();
            $newResult->id = $id;
            $newResult->text = $nom;
            $result[] = $newResult;
        }
    }


}
