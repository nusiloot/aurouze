<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ContactCoordonneeType extends AbstractType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('telephoneFixe', TextType::class, array('label' => 'Tél. Fixe :'))
                ->add('telephoneMobile', TextType::class, array('label' => 'Tél. Mobile :'))
                ->add('fax', TextType::class, array('label' => 'Fax :'))
                ->add('email', TextType::class, array('label' => 'Email :'))
                ->add('siteInternet', TextType::class, array('label' => 'Site internet :'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\ContactCoordonnee',
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'contact_coordonnee';
    }

}
