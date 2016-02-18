<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EtablissementChoiceType
 *
 * @author mathurin
 */

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EtablissementChoiceType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $defaultChoice = array();
        if(isset($options['data']) && isset($options['data']['etablissement'])) {
            $defaultChoice = array($options['data']['etablissement']->getIdentifiant() => $options['data']['etablissement']->getLibelleLong());
        }

        $builder->add('etablissements', 'choice', array("choices" => $defaultChoice,
            'label' => 'Chercher',
            'expanded' => false,
            'multiple' => false,
            "attr" => array("class" => "form-control select2")))

        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'etablissement_choice';
    }

}
