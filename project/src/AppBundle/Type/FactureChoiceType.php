<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FactureChoiceType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $defaultChoice = array();
        if(isset($options['data']) && isset($options['data']['facture'])) {
            $defaultChoice = array($options['data']['facture']->getLibelle() => $options['data']['facture']->getLibelle());
        }
        $builder->add('factures', TextType::class, array("attr" => array("class" => "typeahead form-control", "placeholder" => "Rechercher une facture")));
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'facture_choice';
    }

}
