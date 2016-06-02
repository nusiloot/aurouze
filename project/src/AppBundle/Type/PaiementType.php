<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Manager\PaiementsManager;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use AppBundle\Document\Facture;

use Symfony\Component\Form\FormInterface;

class PaiementType extends AbstractType {

    protected $container;
    protected $dm;

    public function __construct(ContainerInterface $container, DocumentManager $documentManager) {
        $this->container = $container;
        $this->dm = $documentManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
    	
    	$builder
    	->add('moyenPaiement', ChoiceType::class, array('label' => 'Moyen de paiement', 'choices' => array_merge(array(null => null), PaiementsManager::$moyens_paiement_libelles), "attr" => array("class" => "select2 select2-simple")))
    	->add('typeReglement', ChoiceType::class, array('label' => 'Type de paiement', 'choices' => array_merge(array(null => null), PaiementsManager::$nouveau_types_reglements_libelles), "attr" => array("class" => "select2 select2-simple")))
    	->add('libelle', TextType::class, array('label' => 'Libellé'))
    	->add('montant', NumberType::class, array('label' => 'Montant', 'scale' => 2,"attr" => array(
    					'class' => 'nombreSomme')))
    	->add('datePaiement', DateType::class, array(
    			'label' => 'Date de paiement',
    			"attr" => array(
    					'class' => 'input-inline datepicker',
    					'data-provide' => 'datepicker',
    					'data-date-format' => 'dd/mm/yyyy'
    			),
    			'widget' => 'single_text',
    			'format' => 'dd/MM/yyyy'));
    	$builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
    	$builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }    
	
    protected function addElement(FormInterface $form, Facture $facture = null) 
    {
    	$choices = ($facture)? array($facture) : array();
    	
    	$form->add('facture', DocumentType::class, array(
    			'choices' => $choices,
	        	'expanded' => false,
	        	'multiple' => false,
            	'class' => 'AppBundle\Document\Facture',
	        	'attr' => array("class" => "form-control select2")
    	));
    }
    
    function onPreSubmit(FormEvent $event) {
    	$form = $event->getForm();
    	$values = $event->getData();
    	$facture = ($values['facture'])? $this->dm->getRepository('AppBundle:Facture')->find($values['facture']) : null;
    	$this->addElement($form, $facture);
    }
    
    
    function onPreSetData(FormEvent $event) {
    	$form = $event->getForm();
    	$document = $event->getData();
    	$facture = ($document && $document->getFacture())? $document->getFacture() : null;
    	$this->addElement($form, $facture);
    }
    
    
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Paiement',
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'paiement_modification';
    }

}
