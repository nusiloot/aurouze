<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\Bic;


class SepaType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('iban', TextType::class, array('label' => 'IBAN :', 'required' => false, 'constraints' => array(new Iban()), 'empty_data'  => null))
                ->add('bic', TextType::class, array('label' => 'BIC :', 'required' => false, 'constraints' => array(new Bic()), 'empty_data'  => null))
                ->add('rum', TextType::class, array('label' => 'RUM :', 'required' => false, 'empty_data'  => null))
                ->add('actif', CheckboxType::class, array('label' => ' ', 'required' => false, "attr" => array("class" => "switcher", "data-size" => "mini")))
                ->add('date', DateType::class, array(
                		'required' => false,
                		"attr" => array(
                				'class' => 'input-inline datepicker',
                				'data-provide' => 'datepicker',
                				'data-date-format' => 'dd/mm/yyyy'
                		),
                		'widget' => 'single_text',
                		'format' => 'dd/MM/yyyy'
                ));
    }
    
    public function isOk(FormInterface $form)
    {
	    if ($form->get('actif')) {
	        if (!$form->get('iban')) $form->get('iban')->addError(new FormError("Missing" ));
	        if (!$form->get('bic')) $form->get('bic')->addError(new FormError("Missing" ));
	        if (!$form->get('rum')) $form->get('rum')->addError(new FormError("Missing" ));
	        if (!$form->get('date')) $form->get('date')->addError(new FormError("Missing" ));
	    }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Sepa',
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'sepa';
    }

}
