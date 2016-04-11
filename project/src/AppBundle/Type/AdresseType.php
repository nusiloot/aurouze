<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdresseType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('adresse', TextType::class, array('label' => 'Adresse :'))
                ->add('codePostal', TextType::class, array('label' => 'Code postal :'))
                ->add('commune', TextType::class, array('label' => 'Ville :'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Adresse',
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'adresse';
    }

}
