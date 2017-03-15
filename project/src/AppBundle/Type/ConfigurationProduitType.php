<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class ConfigurationProduitType extends AbstractType {

	protected $container;
	protected $dm;
	protected $identifiantProduit;

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
								->add('conditionnement', TextType::class,array('label' => 'Conditionnement :', "required" => false,"attr" => array("placeholder" => 'Conditionnement')))
                ->add('prixHt', NumberType::class, array('label' => 'Prix HT :', 'scale' => 2))
								->add('prixPrestation', NumberType::class, array('label' => 'Prix HT :', 'scale' => 2))
								->add('prixVente', NumberType::class, array('label' => 'Prix HT :', 'scale' => 2))
								->add('actif', CheckboxType::class, array('label' => ' ', 'required' => false, "attr" => array("class" => "switcher", "data-size" => "mini")))
								->add('ordre', NumberType::class, array('label' => 'Ordre :', "required" => false))
								->add('save', SubmitType::class, array('label' => 'Enregistrer', "attr" => array("class" => "btn btn-success pull-right")));

        ;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Document\Produit',
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'produit';
	}
}
