<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Doctrine\ODM\MongoDB\DocumentManager;

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
			->add('timeDebut', TextType::class, array('label' => 'Heure debut', 'attr' => array('class' => 'input-timepicker'), "mapped" => false))
			->add('timeFin', TextType::class, array('label' => 'Heure fin', 'attr' => array('class' => 'input-timepicker'), "mapped" => false))
            ->add('save', SubmitType::class, array('label' => 'Valider', "attr" => array("class" => "btn btn-success"), ));
        ;
        
        $builder->add('techniciens', CollectionType::class, array(
        		'entry_type' => new TechnicienType($this->dm),
        		'allow_add' => true,
        		'allow_delete' => true,
        		'delete_empty' => true,
        		'label' => '',
        ));
        
        $builder->add('prestations', CollectionType::class, array(
        		'entry_type' => new PrestationType($this->dm),
        		'allow_add' => true,
        		'allow_delete' => true,
        		'delete_empty' => true,
        		'label' => '',
        ));
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
}
