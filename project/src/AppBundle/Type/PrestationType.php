<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
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
		->add('id', ChoiceType::class, array('label' => ' ', 'choices'  => array_merge(array('' => ''), $this->getPrestations()), "attr" => array("class" => "form-control select2 select2-simple")))
		->add('nbPassages', TextType::class, array('label' => ' ', "attr" => array("class" => "text-right")))
		;
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Document\Prestation',
		));
	}
        
        public function getPrestations() {
            $configuration = $this->dm->getRepository('AppBundle:Configuration')->findConfiguration();
            $prestationsType = array();
            foreach ($configuration->getPrestations() as $prestation) {
                $prestationsType[$prestation->getId()] = $prestation->getNom();
            } 
            return $prestationsType;    
        }
        
	
	/**
	 * @return string
	 */
	public function getName() 
	{
		return 'prestation';
	}
}