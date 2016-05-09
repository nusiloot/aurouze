<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Type\ContactCoordonneeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use AppBundle\Type\CompteTagType;
use AppBundle\Manager\CompteManager;
use AppBundle\Transformer\TagsTransformer;
use AppBundle\Document\CompteTag;

class CompteType extends AbstractType {

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
                ->add('civilite', ChoiceType::class, array('label' => 'Civilite :', 'required' => false, 'choices' => array_merge(array(null => null), CompteManager::$civilites), "attr" => array("class" => "select2 select2-simple")))
                ->add('nom', TextType::class, array('label' => 'Nom* :'))
                ->add('prenom', TextType::class, array('label' => 'Prenom* :' ,'required' => false))
                ->add('actif', CheckboxType::class, array('label' => 'Actif :', 'required' => false, 'empty_data' => null))
                ->add('save', SubmitType::class, array('label' => 'Enregistrer', "attr" => array("class" => "btn btn-success pull-right")))
                ->add('adresse', AdresseType::class, array('data_class' => 'AppBundle\Document\Adresse'))
                ->add('tags', ChoiceType::class, array(
                    'label' => 'Tags : ',
                    'choices' => $this->getTags(),
                    'expanded' => false,
                    'multiple' => true,
                    'required' => false,
                    'attr' => array("class" => "select2 select2-simple", "multiple" => "multiple"),
                ))
                ->add('contactCoordonnee', ContactCoordonneeType::class, array('data_class' => 'AppBundle\Document\ContactCoordonnee'));

        $builder->add('sameContact', CheckboxType::class, array('label' => 'Même contact société', 'required' => false, 'empty_data' => null, "attr" => array("class" => "collapse-checkbox", "data-target" => "#collapseContact")));
        $builder->add('sameAdresse', CheckboxType::class, array('label' => 'Même adresse société', 'required' => false, 'empty_data' => null, "attr" => array("class" => "collapse-checkbox", "data-target" => "#collapseAdresse")));

        $builder->get('tags')->addModelTransformer(new TagsTransformer($this->dm, $builder->getData()));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Compte',
        ));
    }

    public function getTags() {

        $result = array();
        foreach (CompteManager::$tagsCompteLibelles as $key => $tag) {

            $result[$key] = $tag;
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'compte';
    }

}
