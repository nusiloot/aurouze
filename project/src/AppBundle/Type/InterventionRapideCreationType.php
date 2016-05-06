<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Type\PrestationType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ODM\MongoDB\DocumentManager;

class InterventionRapideCreationType extends AbstractType {

    protected $dm;

    public function __construct(DocumentManager $documentManager) {
        $this->dm = $documentManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('dateDebut', DateType::class, array(
                    'label' => 'Date début :',
                    "attr" => array(
                        'class' => 'input-inline datepicker',
                        'data-provide' => 'datepicker',
                        'data-date-format' => 'dd/mm/yyyy'
                    ),
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy'))
                ->add('technicien', DocumentType::class, array(
                    "choices" => array_merge(array('' => ''), $this->getComptes()),
                    'label' => 'Technicien :',
                    'class' => 'AppBundle\Document\Compte',
                    'expanded' => false,
                    'multiple' => false,
                    "attr" => array("class" => "select2 select2-simple")))
                ->add('prestations', CollectionType::class, array(
                    'entry_type' => new PrestationType($this->dm),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'label' => ''))
                ->add('commercial', DocumentType::class, array(
                    "choices" => array_merge(array('' => ''), $this->getComptes()),
                    'label' => 'Commercial :',
                    'class' => 'AppBundle\Document\Compte',
                    'expanded' => false,
                    'multiple' => false,
                    "attr" => array("class" => "select2 select2-simple")))
                ->add('duree', TextType::class, array('label' => 'Durée du contrat :'))
                ->add('save', SubmitType::class, array('label' => 'Planifier le passage', "attr" => array("class" => "btn btn-success pull-right")));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Contrat'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'interventionRapide';
    }

    public function getComptes() {
        return $this->dm->getRepository('AppBundle:Compte')->findAllUtilisateursActif();
    }

}
