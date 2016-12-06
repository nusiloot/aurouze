<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\CallbackTransformer;

class ProduitPassageType extends AbstractType {

	protected $dm;
	protected $options;

	public function __construct(DocumentManager $documentManager,array $options)
	{
		$this->dm = $documentManager;
		$this->options = $options;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$phoenix = (isset($this->options['phoenix']))? " phoenix" : "";
		$builder
		->add('identifiant', ChoiceType::class, array('label' => ' ', 'choices'  => array_merge(array('' => ''), $this->getProduits()), "attr" => array("class" => "form-control select2 select2-simple ".$phoenix,"placeholder" => 'Choisir un produit')))
		->add('nbUtilisePassage', NumberType::class, array('label' => ' ',"required" => false, "attr" => array("class" => "text-right ".$phoenix)))

		;

		$builder->get('nbUtilisePassage')
                ->addModelTransformer(new CallbackTransformer(
                        function ($originalDescription) {
                    return (!$originalDescription)? null : $originalDescription;
                }, function ($submittedDescription) {
                    return (!$submittedDescription)? 0 : $submittedDescription;
                }));
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
