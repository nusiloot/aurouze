<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Type\Adresse;
use AppBundle\Type\ContactCoordonneeType;
use AppBundle\Manager\EtablissementManager;

class EtablissementType extends AbstractType {

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
        $builder
                ->add('raisonSociale', TextType::class, array('label' => 'Raison sociale :'))
                ->add('nom', TextType::class, array('label' => 'Nom :'))
                ->add('type', ChoiceType::class, array('label' => 'Type :', 'choices' => array_merge(array('' => ''), $this->getTypes()), "attr" => array("class" => "select2 select2-simple")))
                ->add('save', SubmitType::class, array('label' => 'Enregistrer', "attr" => array("class" => "btn btn-success pull-right")))
        		->add('adresse', AdresseType::class, array('data_class' => 'AppBundle\Document\Adresse'))
        		->add('contactCoordonnee', ContactCoordonneeType::class, array('data_class' => 'AppBundle\Document\ContactCoordonnee'))
                ->add('commentaire', TextareaType::class, array('label' => 'Commentaire :', "attr" => array("class" => "form-control", "rows" => 6)));
       
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Etablissement',
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'etablissement';
    }

    public function getTypes() {
        return EtablissementManager::$type_libelles;
    }

}
