<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ContratType extends AbstractType {
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder
		->add('commercial', TextType::class)
		->add('technicien', TextType::class)
		->add('type_contrat', ChoiceType::class, array('choices'  => array()))
		->add('type_prestation', ChoiceType::class, array('choices'  => array()))
		->add('localisation_traitement', TextareaType::class)
		->add('date_debut', DateType::class)
		->add('duree', IntegerType::class)
		->add('duree_garantie', IntegerType::class)
		->add('nb_passage', IntegerType::class)
		->add('duree_passage', IntegerType::class)
		->add('frequence_facturation', IntegerType::class)
		->add('type_facturation', ChoiceType::class, array('choices'  => array()))
		->add('prix_ht', NumberType::class, array('scale' => 2));
	}
	
	/**
	 * @return string
	 */
	public function getName() 
	{
		return 'contrat';
	}
}