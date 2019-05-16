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
     * @Route("/attachement/{id}/documents/{all}", name="attachements_entite", defaults={"all" = null})
     */
    public function visualisationAction(Request $request, $id, $all = null) {

      $societe = $this->get('societe.manager')->getRepository()->findOneById($id);

      if($societe){
          return $this->redirectToRoute('attachements_societe', array('id' => $societe->getId(), 'all' => $all));
      }
      $etablissement = $this->get('etablissement.manager')->getRepository()->findOneById($id);
      if($etablissement){
          return $this->redirectToRoute('attachements_etablissement', array('id' => $etablissement->getId()));
      }
    }

    /**
     * @Route("/attachement/societe/{id}/documents/{all}", name="attachements_societe", defaults={"all" = null})
     * @ParamConverter("societe", class="AppBundle:Societe")
     */
    public function visualisationSocieteAction(Request $request, $societe, $all = null) {


      ini_set('memory_limit', '-1');
      $dm = $this->get('doctrine_mongodb')->getManager();
      $attachement = new Attachement();
      $attachementRepository = $this->get('attachement.manager')->getRepository();
      $actif = $societe;

      $allAttachements = $attachementRepository->findBySocieteAndEtablissement($societe);


      $attachements = ($all)? $allAttachements : $attachementRepository->findBy(array('societe' => $societe), array('updatedAt' => 'DESC'));
      $nbTotalAttachements = count($allAttachements);
      $urlForm = $this->generateUrl('societe_upload_attachement', array('id' => $societe->getId()));

      $form = $this->createForm(new AttachementType($dm), $attachement, array(
              'action' => $urlForm,
              'method' => 'POST',
      ));
      return $this->render('attachement/listing.html.twig', array('attachements'  => $attachements, 'societe' => $societe, 'etablissement' => null, 'actif' => $actif, 'urlForm' => $urlForm, 'form' => $form->createView(), 'all' => $all, 'nbTotalAttachements' => $nbTotalAttachements));
    }

    /**
     * @Route("/attachement/etablissement/{id}/documents", name="attachements_etablissement")
     * @ParamConverter("etablissement", class="AppBundle:Etablissement")
     */
    public function visualisationEtablissementAction(Request $request, $etablissement) {

      ini_set('memory_limit', '-1');
      $dm = $this->get('doctrine_mongodb')->getManager();
      $attachement = new Attachement();
      $attachementRepository = $this->get('attachement.manager')->getRepository();
      $societe = $etablissement->getSociete();
      $attachements = $attachementRepository->findBy(array('etablissement' => $etablissement), array('updatedAt' => 'DESC'));
      $actif = $etablissement;
      $urlForm = $this->generateUrl('etablissement_upload_attachement', array('id' => $etablissement->getId()));

      $nbTotalAttachements = count($attachementRepository->findBySocieteAndEtablissement($societe));

      $form = $this->createForm(new AttachementType($dm), $attachement, array(
              'action' => $urlForm,
              'method' => 'POST',
      ));

      return $this->render('attachement/listing.html.twig', array('attachements'  => $attachements, 'societe' => $societe, 'etablissement' => $etablissement, 'actif' => $actif, 'urlForm' => $urlForm, 'form' => $form->createView(), 'nbTotalAttachements' => $nbTotalAttachements , 'all' => null));
    }


    /**
    * @Route("/attachement/{id}/supprimer", name="attachement_delete")
    */
    public function attachementDeleteAction(Request $request, $id) {
       $attachement = $this->get('attachement.manager')->getRepository()->find($id);

       $noremove = $request->get('noremove',false);
       $entite = $attachement->getSociete();
       if(!$entite){
         $entite = $attachement->getEtablissement();
       }
       if(!$entite){
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

       return $this->redirectToRoute('attachements_entite', array('id' => $entite->getId()));
   }

   /**
   * @Route("/societe/attachement/{id}/ajout", name="societe_upload_attachement")
   */
   public function attachementSocieteUploadAction(Request $request, $id) {
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
        return $this->redirectToRoute('attachements_entite', array('id' => $societe->getId()));
      }
  }

   /**
   * @Route("/etablissement/attachement/{id}/ajout", name="etablissement_upload_attachement")
   */
   public function attachementEtablissementUploadAction(Request $request, $id) {
      ini_set ('gd.jpeg_ignore_warning', 1);
      $attachement = new Attachement();
      $dm = $this->get('doctrine_mongodb')->getManager();
      $etablissement = $this->get('etablissement.manager')->getRepository()->find($id);
      $uploadAttachementForm = $this->createForm(new AttachementType($dm), $attachement, array(
          'action' => $this->generateUrl('etablissement_upload_attachement', array('id' => $id)),
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
          return $this->redirectToRoute('attachements_entite', array('id' => $etablissement->getId()));
      }
  }

}
