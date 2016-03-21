<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class FactureLigneType extends AbstractType {

	/** @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
		    ->add('libelle', TextType::class, array('attr' => array('placeholder' => "Libellé")))
		    ->add('quantite', TextType::class, array('attr' => array('placeholder' => "Quantité")))
		    ->add('prixUnitaire', TextType::class, array('attr' => array('placeholder' => "Prix unitaire HT")));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Document\FactureLigne',
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'facture_ligne';
	}
}
