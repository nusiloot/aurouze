<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class PrestationType extends AbstractType {
	
	protected $container;
	protected $dm;
	
	public function __construct(ContainerInterface $container, DocumentManager $documentManager)
	{
		$this->container = $container;
		$this->dm = $documentManager;
	}
	
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder
		->add('prestationType', ChoiceType::class, array('choices'  => array_merge(array('' => ''), $this->container->getParameter('prestations_type')), "attr" => array("class" => "form-control select2 select2-simple")))
		->add('animal', ChoiceType::class, array('choices'  => array_merge(array('' => ''), $this->container->getParameter('animaux')), "attr" => array("class" => "form-control select2 select2-simple")))
		->add('commentaire', TextareaType::class, array("attr" => array("class" => "form-control")));
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Document\Prestation',
		));
	}
	
	/**
	 * @return string
	 */
	public function getName() 
	{
		return 'prestation';
	}
}