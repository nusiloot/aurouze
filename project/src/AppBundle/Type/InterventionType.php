<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Type\PrestationType;

class InterventionType extends AbstractType {
	
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
		->add('facturable', CheckboxType::class)
		;
		
		$builder->add('prestations', CollectionType::class, array(
				'entry_type' => new PrestationType($this->container, $this->dm),
				'allow_add' => true,
				'allow_delete' => true,
				'delete_empty' => true,
				'label' => ' ',
		));
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Document\Intervention',
		));
	}
	
	/**
	 * @return string
	 */
	public function getName() 
	{
		return 'intervention';
	}
}