<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Manager\ContratManager;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ReconductionFiltresType extends AbstractType {
	
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$typesContrat = array_merge(array(null => null), ContratManager::$types_contrats_reconductibles);
		$dateRecondution = new \DateTime();
		$builder->add('typeContrat', ChoiceType::class, array('label' => 'Type de contrat',
                		'choices' => $typesContrat,
                		"attr" => array("class" => "select2 select2-simple typeContrat")));
		$builder->add('dateRenouvellement', DateType::class, array('required' => true,
                		"attr" => array('class' => 'input-inline datepicker dateRenouvellement',
                				'data-provide' => 'datepicker',
                				'data-date-format' => 'dd/mm/yyyy'
                		),
                		'data' => $dateRecondution,
                		'widget' => 'single_text',
                		'format' => 'dd/MM/yyyy',
		));
		$builder->add('societe', TextType::class, array("required" => false, "attr" => array("class" => "typeahead form-control", "placeholder" => (isset($options['data']) && isset($options['data']['societe']) && $options['data']['societe']->getIntitule()) ? $options['data']['societe']->getIntitule() : "Rechercher une société")));
		$builder->add('save', SubmitType::class, array('label' => 'Filtrer', "attr" => array("class" => "btn btn-success pull-right")));
	}
        
	
	/**
	 * @return string
	 */
	public function getName() 
	{
		return 'reconduction_filtres';
	}
}