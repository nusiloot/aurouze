<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Document\User;
use AppBundle\Document\Prestation;
use Symfony\Component\Form\CallbackTransformer;
use AppBundle\Transformer\PrestationTransformer;

class PassageCreationType extends AbstractType
{
    protected $dm;

    public function __construct(DocumentManager $documentManager) {
        $this->dm = $documentManager;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateDebut', DateType::class, array(
				"attr" => array(
						'class' => 'input-inline datepicker',
        				'data-provide' => 'datepicker',
        				'data-date-format' => 'dd/mm/yyyy'
				),
				'widget' => 'single_text',
				'format' => 'dd/MM/yyyy'
			))
            ->add('dateFin', DateType::class, array(
				"attr" => array(
						'class' => 'input-inline datepicker',
        				'data-provide' => 'datepicker',
        				'data-date-format' => 'dd/mm/yyyy'
				),
				'widget' => 'single_text',
				'format' => 'dd/MM/yyyy'
			))
			->add('timeDebut', TextType::class, array('label' => 'Heure debut', 'attr' => array('class' => 'input-timepicker')))
			->add('timeFin', TextType::class, array('label' => 'Heure fin', 'attr' => array('class' => 'input-timepicker')))
            ->add('save', SubmitType::class, array('label' => 'Valider', "attr" => array("class" => "btn btn-success"), ));
        ;
        
        $builder->add('techniciens', DocumentType::class, array(
            	'choices' => $this->getUsers(User::USER_TYPE_TECHNICIEN),
                'class' => 'AppBundle\Document\User',
        		'expanded' => false, 
        		'multiple' => true,
        		'attr' => array("class" => "select2 select2-simple", "multiple" => "multiple", "style" => "width:100%;")
        ));
        
        $builder->add('prestations', ChoiceType::class, array(
            		'choices' => $this->getPrestations(),
	        		'expanded' => false, 
	        		'multiple' => true,
        			'attr' => array("class" => "select2 select2-simple", "multiple" => "multiple", "style" => "width:100%;"),
        ));
        $builder->get('prestations')->addModelTransformer(new PrestationTransformer($this->dm));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Passage'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'passage';
    }
    
    public function getUsers($type) 
    {
    	return $this->dm->getRepository('AppBundle:User')->findAllActif();
    }
    
    public function getPrestations() 
    {
    	return $this->dm->getRepository('AppBundle:Configuration')->findConfiguration()->getPrestations()->toArray();
    }
}
