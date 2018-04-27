<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Type\EtablissementChoiceType;
use AppBundle\Type\SocieteType;
use AppBundle\Type\AttachementType;
use AppBundle\Document\Societe;
use AppBundle\Document\Etablissement;
use AppBundle\Manager\EtablissementManager;
use AppBundle\Document\Attachement;

class AttachementController extends Controller {

    /**
     * @Route("/derniers-documents", name="attachements_last")
     */
    public function indexAction(Request $request) {

    	$dm = $this->get('doctrine_mongodb')->getManager();
        $lastAttachements = $this->get('attachement.manager')->getRepository()->findBy(array(), array('updatedAt' => 'DESC'), 10);
    	return $this->render('attachement/index.html.twig',array('lastAttachements' => $lastAttachements));
    }


    /**
     * @Route("/attachement/etablissement/{id}/documents", name="attachements_etablissement")
     * @ParamConverter("etablissement", class="AppBundle:Etablissement")
     */
    public function visualisationAction(Request $request, $etablissement) {

      $dm = $this->get('doctrine_mongodb')->getManager();

      $formEtablissement = $this->createForm(EtablissementChoiceType::class, null, array(
          'action' => $this->generateUrl('passage_etablissement_choice'),
          'method' => 'GET',
      ));
      $geojson = $this->buildGeoJson($etablissement);

      $attachement = new Attachement();
      $form = $this->createForm(new AttachementType($dm), $attachement, array(
            'action' => $this->generateUrl('etablissement_upload_attachement', array('id' => $etablissement->getId())),
            'method' => 'POST',
       ));
       return $this->render('attachement/etablissement.html.twig', array('etablissement' => $etablissement, 'form' => $form->createView(), 'formEtablissement' => $formEtablissement->createView(), 'geojson' => $geojson ));
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

       return $this->redirectToRoute('attachements_etablissement', array('id' => $societe->getId()));
   }

   /**
   * @Route("/etablissement/attachement/{id}/ajout", name="etablissement_upload_attachement")
   */
   public function attachementUploadAction(Request $request, $id) {
      ini_set ('gd.jpeg_ignore_warning', 1);
      $attachement = new Attachement();
      $dm = $this->get('doctrine_mongodb')->getManager();
      $etablissement = $this->get('etablissement.manager')->getRepository()->find($id);
      $uploadAttachementForm = $this->createForm(new AttachementType($dm), $attachement, array(
          'action' => $this->generateUrl('societe_upload_attachement', array('id' => $id)),
          'method' => 'POST',
      ));

      if ($request->isMethod('POST')) {
          $uploadAttachementForm->handleRequest($request);
          if($uploadAttachementForm->isValid()){

            $attachement->setEtablissement($etablissement);
            $dm->persist($attachement);
            $etablissement->addAttachement($attachement);
            $dm->flush();

            $attachement->convertBase64AndRemove();
            $dm->flush();

          }
          return $this->redirectToRoute('attachements_etablissement', array('id' => $etablissement->getId()));
      }
  }

  private function buildGeoJson($document) {
      $geojson = new \stdClass();
      $geojson->type = "FeatureCollection";
      $geojson->features = array();
      $coordinates = $document->getAdresse()->getCoordonnees();

      $feature = new \stdClass();
      $feature->type = "Feature";
      $feature->properties = new \stdClass();
      $feature->properties->_id = $document->getId();
      $feature->properties->color = "#fff";
      $feature->properties->colorText = "#000";
      if (!$coordinates->getLon() || !$coordinates->getLat()) {
              continue;
      }
      $feature->properties->nom = $document->getNom();
      $feature->properties->icon = 'mdi-' . $document->getIcon();
      $feature->geometry = new \stdClass();
      $feature->geometry->type = "Point";
      $feature->geometry->coordinates = array($coordinates->getLon(), $coordinates->getLat());
      $geojson->features[] = $feature;

      return $geojson;
  }

}
