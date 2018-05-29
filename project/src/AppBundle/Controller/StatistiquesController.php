<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Manager\PaiementsManager;
use AppBundle\Manager\ContratManager;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class StatistiquesController extends Controller
{

	/**
	 * @Route("/statistiques", name="statistiques")
	 */
	public function indexAction() {
		$exportForms = $this->createExportsForms();
		return $this->render('statistiques/index.html.twig', array('exportForms' => $exportForms));
	}

    private function createExportsForms()
    {

      $exportsTypes = PaiementsManager::$types_exports;
      $exportForms = array();
      foreach ($exportsTypes as $exporttype => $type_export) {
        $exportForms[$exporttype] = new \stdClass();
        $exportForms[$exporttype]->type = $exporttype;
        $exportForms[$exporttype]->libelle = $type_export['libelle'];
        $exportForms[$exporttype]->picto = $type_export['picto'];
        $exportForms[$exporttype]->pdf = $type_export['pdf'];
        $formBuilder = $this->createFormBuilder(array());
            $formBuilder->add('dateDebut', DateType::class, array('required' => true,
                "attr" => array('class' => 'input-inline datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'dd/mm/yyyy'
                    ),
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'label' => 'Date de début* :',
            ));
            if($exporttype != PaiementsManager::TYPE_EXPORT_PCA){
              $formBuilder->add('dateFin', DateType::class, array('required' => true,
                "attr" => array('class' => 'input-inline datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'dd/mm/yyyy'
                    ),
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'label' => 'Date de fin* :',
            ));
          }

          if(in_array($exporttype, array(PaiementsManager::TYPE_EXPORT_CONTRATS, PaiementsManager::TYPE_EXPORT_STATS))) {
            $commerciaux =$this->get('doctrine_mongodb')->getManager()->getRepository('AppBundle:Compte')->findAllUtilisateursCommercial();
            $formBuilder->add('commercial', DocumentType::class, array(
                'required' => false,
                "choices" => array_merge(array('' => ''), $commerciaux),
                'label' => 'Commercial :',
                'class' => 'AppBundle\Document\Compte',
                'expanded' => false,
                'multiple' => false,
                "attr" => array("class" => "select2 select2-simple", "data-placeholder" => "Séléctionner un commercial", "style"=> "width:100%;")));
        	}
        if($exporttype == PaiementsManager::TYPE_EXPORT_RENTABILITE){
        	$formBuilder->add('societe', TextType::class, array('required' => false, "attr" => array("class" => "typeahead form-control", "placeholder" => "Rechercher une société")));
        }
        if($exporttype == PaiementsManager::TYPE_EXPORT_COMMERCIAUX){
        	$formBuilder->add('statut', ChoiceType::class, array('required' => false, 'label' => 'Statut :', 'choices' => array_merge(array('' => ''), ContratManager::$statuts_libelles), "attr" => array("class" => "select2 select2-simple")));
        }
          if($type_export['pdf']){
            $formBuilder->add('pdf', CheckboxType::class, array('label' => 'PDF', 'required' => false, 'label_attr' => array('class' => 'small')));
          }
        $formBuilder->setAction($this->generateUrl($exporttype.'_export'));
        $form = $formBuilder->getForm();

        $exportForms[$exporttype]->form = $form->createView();
      }
      return $exportForms;
    }
}
