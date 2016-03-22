<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Type\PrestationType;
use AppBundle\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;

class ContratType extends AbstractType {

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
                ->add('typeContrat', ChoiceType::class, array('label' => 'Type de contrat :', 'choices' => array_merge(array('' => ''), $this->container->getParameter('contrat_type')), "attr" => array("class" => "select2 select2-simple")))
                ->add('nomenclature', TextareaType::class, array('label' => 'Nomenclature :', "attr" => array("class" => "form-control", "rows" => 6)))
                ->add('duree', TextType::class, array('label' => 'Durée du contrat :'))
                ->add('duree_garantie', TextType::class, array('label' => 'Durée de la garantie :'))
                ->add('nbPassage', TextType::class, array('label' => 'Nombre total de passage :'))
                ->add('dureePassage', TextType::class, array('label' => 'Durée estimative d\'un passage :'))
                ->add('prixHt', NumberType::class, array('label' => 'Prix HT :','scale' => 2))
                ->add('save', SubmitType::class, array('label' => 'Suivant', "attr" => array("class" => "btn btn-success pull-right")));

        $builder->add('commercial', DocumentType::class, array(
            "choices" => array_merge(array('' => ''), $this->getUsers(User::USER_TYPE_COMMERCIAL)),
            'label' => 'Commercial :',
            'class' => 'AppBundle\Document\User',
            'expanded' => false,
            'multiple' => false,
            "attr" => array("class" => "select2 select2-simple")));

        $builder->add('technicien', DocumentType::class, array(
            "choices" => array_merge(array('' => ''), $this->getUsers(User::USER_TYPE_TECHNICIEN)),
            'label' => 'Technicien :',
            'class' => 'AppBundle\Document\User',
            'expanded' => false,
            'multiple' => false,
            "attr" => array("class" => "select2 select2-simple")));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Contrat',
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'contrat';
    }

    public function getUsers($type) {
        return $this->dm->getRepository('AppBundle:User')->findAllByType($type);
    }

}
