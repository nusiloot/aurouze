<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;

class ContratAcceptationType extends AbstractType {

    protected $dm;

    public function __construct(DocumentManager $documentManager) {

        $this->dm = $documentManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('dateDebut', DateType::class, array(
				"attr" => array(
						'class' => 'input-inline datepicker',
        				'data-provide' => 'datepicker',
        				'data-date-format' => 'dd/mm/yyyy'
				),
				'widget' => 'single_text',
				'format' => 'dd/MM/yyyy'
		))->add('dateAcceptation', DateType::class, array(
				"attr" => array(
						'class' => 'input-inline datepicker',
        				'data-provide' => 'datepicker',
        				'data-date-format' => 'dd/mm/yyyy'
				),
				'widget' => 'single_text',
				'format' => 'dd/MM/yyyy'
		))
                
                ->add('save', SubmitType::class, array('label' => 'Acceptation du contrat', "attr" => array("class" => "btn btn-success pull-right")));

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
        return 'contrat_acceptation';
    }

   

}
