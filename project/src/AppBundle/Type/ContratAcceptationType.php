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
use AppBundle\Document\Contrat;
use AppBundle\Document\Compte;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;

class ContratAcceptationType extends AbstractType {

    protected $dm;
    protected $contrat;

    public function __construct(DocumentManager $documentManager, Contrat $c) {
        $this->dm = $documentManager;
        $this->contrat = $c;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $readonly = array();
        if (!$this->contrat->isModifiable()) {
            $readonly = array('readonly' => 'readonly');
        }
        if (!$this->contrat->isEnAttenteAcceptation() && !$this->contrat->isBrouillon()) {
           $builder->add('nomenclature', TextareaType::class, array('label' => 'Nomenclature* :', "attr" => array("class" => "form-control", "rows" => 6)));
        }
        
        $builder->add('dateDebut', DateType::class, array(
            "attr" => array_merge(array(
                'class' => 'input-inline datepicker',
                'data-provide' => 'datepicker',
                'data-date-format' => 'dd/mm/yyyy'
                    ), $readonly),
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'label' => 'Date de début* :',
        ))->add('dateAcceptation', DateType::class, array(
            "attr" => array_merge(array(
                'class' => 'input-inline datepicker',
                'data-provide' => 'datepicker',
                'data-date-format' => 'dd/mm/yyyy'
                    ), $readonly),
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy',
            'label' => 'Date d\'acceptation* :',
        ));

        $builder->add('technicien', DocumentType::class, array(
            "choices" => array_merge(array('' => ''), $this->getTechniciens()),
            'label' => 'Technicien* :',
            'class' => 'AppBundle\Document\Compte',
            'expanded' => false,
            'multiple' => false,
            "attr" => array("class" => "select2 select2-simple")));

        $builder->add('commercial', DocumentType::class, array(
            "choices" => array_merge(array('' => ''), $this->getCommerciaux()),
            'label' => 'Commercial* :',
            'class' => 'AppBundle\Document\Compte',
            'expanded' => false,
            'multiple' => false,
            "attr" => array("class" => "select2 select2-simple")));

        $builder->add('commentaire', TextareaType::class, array('label' => 'Commentaire :', "required" => false, "attr" => array("class" => "form-control", "rows" => 12)));
        $builder->add('referenceClient', TextType::class, array('label' => 'Numéro de commande :', 'required' => false));

        $builder->add('save', SubmitType::class, array('label' => ($this->contrat->isEnAttenteAcceptation()) ? 'Acceptation du contrat' : 'Modification du contrat', "attr" => array("class" => "btn btn-success pull-right")));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Contrat',
        ));
    }

    public function getTechniciens() {
        return $this->dm->getRepository('AppBundle:Compte')->findAllUtilisateursTechnicien();
    }

    public function getCommerciaux() {
        return $this->dm->getRepository('AppBundle:Compte')->findAllUtilisateursCommercial();
    }

    /**
     * @return string
     */
    public function getName() {
        return 'contrat_acceptation';
    }

}
