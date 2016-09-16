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
use AppBundle\Manager\PaiementsManager;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RelanceType extends AbstractType {

	protected $factures;

	public function __construct($factures = array())
	{
		$this->factures = $factures;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		foreach ($this->factures as $facture) {
        		$builder->add($facture->getId(), CheckboxType::class, array('label' => ' ', 'required' => false, 'label_attr' => array('class' => 'small')));
    }
    $builder->add('typeRelance', ChoiceType::class, array('required' => false, 'choices' => array_merge(array(null => null), PaiementsManager::$typesRelance), "attr" => array("class" => "select2 select2-simple', 'placeholder' => 'Type de relance'")));
    $builder->add('relancer', 'submit', array('label' => "Relancer", "attr" => array("class" => "btn btn-primary pull-right")));
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return 'relance';
	}
}
