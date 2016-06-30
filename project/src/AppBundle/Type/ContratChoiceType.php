<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ContratChoiceType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $defaultChoice = array();
        if(isset($options['data']) && isset($options['data']['contrat'])) {
            $defaultChoice = array($options['data']['contrat']->getIntitule() => $options['data']['contrat']->getIntitule());
        }
        $builder->add('contrats', TextType::class, array("attr" => array("class" => "typeahead form-control", "placeholder" => "Rechercher un contrat")));
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'contrat_choice';
    }

}
