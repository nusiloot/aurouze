<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EtablissementChoiceType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $defaultChoice = array();
        if(isset($options['data']) && isset($options['data']['etablissement'])) {
            $defaultChoice = array($options['data']['etablissement']->getIdentifiant() => $options['data']['etablissement']->getIntitule());
        }

        $builder->add('etablissements', ChoiceType::class, array("choices" => $defaultChoice,
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
        return 'etablissement_choice';
    }

}
