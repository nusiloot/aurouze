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
                ->add('telephoneFixe', TextType::class, array('label' => 'Tél. Fixe :','required' => false))
                ->add('telephoneMobile', TextType::class, array('label' => 'Tél. Mobile :','required' => false))
                ->add('fax', TextType::class, array('label' => 'Fax :','required' => false))
                ->add('email', TextType::class, array('label' => 'Email :','required' => false))
                ->add('siteInternet', TextType::class, array('label' => 'Site internet :','required' => false));
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
