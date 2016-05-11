<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class ProduitType extends AbstractType {
	
	protected $dm;
	
	public function __construct(DocumentManager $documentManager)
	{
		$this->dm = $documentManager;
	}
	
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		$builder
		->add('identifiant', ChoiceType::class, array('label' => ' ', 'choices'  => array_merge(array('' => ''), $this->getProduits()), "attr" => array("class" => "form-control select2 select2-simple","placeholder" => 'Choisir un produit')))
		->add('nbTotalContrat', NumberType::class, array('label' => ' ', "attr" => array("class" => "text-right")))
                ->add('nbPremierPassage', NumberType::class, array('label' => ' ', 'required' => false, "attr" => array("class" => "text-right")))
		;
	}
	
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Document\Produit',
		));
	}
        
        public function getProduits() {
            return $this->dm->getRepository('AppBundle:Configuration')->findConfiguration()->getProduitsArray();
            
        }
        
	
	/**
	 * @return string
	 */
	public function getName() 
	{
		return 'prestation';
	}
}