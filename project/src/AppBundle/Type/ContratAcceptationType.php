<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Document\Contrat;
use AppBundle\Document\User;
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
        if (!$this->contrat->isEnAttenteAcceptation()) {
            $readonly = array('readonly' => 'readonly');
        }
        $builder->add('dateDebut', DateType::class, array(
            "attr" => array_merge(array(
                'class' => 'input-inline datepicker',
                'data-provide' => 'datepicker',
                'data-date-format' => 'dd/mm/yyyy'
                    ), $readonly),
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy'
        ))->add('dateAcceptation', DateType::class, array(
            "attr" => array_merge(array(
                'class' => 'input-inline datepicker',
                'data-provide' => 'datepicker',
                'data-date-format' => 'dd/mm/yyyy'
                    ), $readonly),
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy'
        ));
        
        $builder->add('technicien', DocumentType::class, array(
            "choices" => array_merge(array('' => ''), $this->getUsers(User::USER_TYPE_TECHNICIEN)),
            'label' => 'Technicien :',
            'class' => 'AppBundle\Document\User',
            'expanded' => false,
            'multiple' => false,
            "attr" => array("class" => "select2 select2-simple")));
        
        $builder->add('commentaire', TextareaType::class, array('label' => 'Commentaire :', "attr" => array("class" => "form-control", "rows" => 6)));


        $builder->add('save', SubmitType::class, array('label' => 'Acceptation du contrat', "attr" => array("class" => "btn btn-success pull-right")));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Contrat',
        ));
    }
    
    public function getUsers($type) {
        return $this->dm->getRepository('AppBundle:User')->findAllByType($type);
    }

    /**
     * @return string
     */
    public function getName() {
        return 'contrat_acceptation';
    }

}
