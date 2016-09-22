<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Document\Passage;
use AppBundle\Document\Compte;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;

class PassageAnnulationType extends AbstractType {

    protected $dm;
    protected $passage;

    public function __construct(DocumentManager $documentManager, Passage $p) {
        $this->dm = $documentManager;
        $this->passage = $p;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder
                ->add('commentaire', TextareaType::class, array('label' => "Commentaire d'annulation :", "required" => false, "attr" => array("class" => "form-control", "rows" => 12)));


        $builder->add('save', SubmitType::class, array('label' => 'Annuler le passage', "attr" => array("class" => "btn btn-danger pull-right")));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Passage',
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'passage_annulation';
    }

}
