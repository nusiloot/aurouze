<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class ConfigurationType extends AbstractType {

    protected $container;
    protected $dm;

    public function __construct(ContainerInterface $container, DocumentManager $documentManager) {
        $this->container = $container;
        $this->dm = $documentManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('prestations', CollectionType::class, array(
            'entry_type' => new ConfigurationPrestationType($this->container, $this->dm),
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'label' => ' ',
        ))->add('produits', CollectionType::class, array(
            'entry_type' => new ConfigurationProduitType($this->container, $this->dm),
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'label' => ' ',
        ))->add('save', SubmitType::class, array('label' => 'Enregistrer', "attr" => array("class" => "btn btn-success pull-right")));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Configuration',
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'configuration';
    }

}
