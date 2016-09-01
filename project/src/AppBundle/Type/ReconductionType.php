<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Manager\ContratManager;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ReconductionType extends AbstractType {
	
	protected $contrats;
	
	public function __construct($contrats = array())
	{
		$this->contrats = $contrats;
	}
	
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		foreach ($this->contrats as $contrat) {
        	$builder->add($contrat->getId(), CheckboxType::class, array('label' => ' ', 'required' => false, 'label_attr' => array('class' => 'small')));
      	}
      	$builder->add('augmentation', TextType::class, array('required' => false, 'attr' => array('placeholder' => 'Augmentation (%)')));
      	$builder->add('reconduire', 'submit', array('label' => "Reconduire", "attr" => array("class" => "btn btn-primary pull-right")));
	}
        
	
	/**
	 * @return string
	 */
	public function getName() 
	{
		return 'reconduction';
	}
}