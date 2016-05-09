<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SocieteChoiceType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $defaultChoice = array();
        if(isset($options['data']) && isset($options['data']['societe'])) {
            $defaultChoice = array($options['data']['societe']->getIntitule() => $options['data']['societe']->getIntitule());
        }
        $builder->add('actif', CheckboxType::class, array('label' => 'inclure les sociétés suspendues', 'required' => false, 'empty_data' => null, 'attr'=> array("data-search-actif" => "1")));
        $builder->add('societes', ChoiceType::class, array("choices" => $defaultChoice,
            'label' => 'Chercher',
            'expanded' => false,
            'multiple' => false,
            'choices_as_values' => true,
            "attr" => array("class" => "form-control select2")))

        ;
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'societe_choice';
    }

}
