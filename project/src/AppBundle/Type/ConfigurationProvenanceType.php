<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class ConfigurationProvenanceType extends AbstractType {
	
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
                ->add('nom', TextType::class)
        ;
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Document\Provenance',
		));
	}
	
	/**
	 * @return string
	 */
	public function getName() 
	{
		return 'provenance';
	}
}