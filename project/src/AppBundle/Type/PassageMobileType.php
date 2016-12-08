<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ODM\MongoDB\DocumentManager;

class PassageMobileType extends AbstractType
{

    protected $dm;
    protected $passageId;

    public function __construct(DocumentManager $documentManager,$passageId) {
        $this->dm = $documentManager;
        $this->passageId = $passageId;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextareaType::class, array('label' => 'Constat :', 'required' => false, "attr" => array("class" => " phoenix", "rows" => 10)))
            ->add('duree', TimeType::class, array('label' => 'Durée effective du passage* :', 'attr' => array('class' => " phoenix", "data-clear-btn" => "true")))
            ->add('save', SubmitType::class, array('label' => 'Valider', "attr" => array("class" => " phoenix")));
        ;

        $builder->add('produits', CollectionType::class, array(
            'entry_type' => new ProduitPassageMobileType($this->dm),
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'label' => '',
        ));

        $builder->add('niveauInfestation', CollectionType::class, array(
            'entry_type' => new NiveauInfestationPassageMobileType($this->dm),
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'label' => '',
        ));


        $builder->add('nettoyages', ChoiceType::class, array(
        		'label' => 'Nettoyage : ',
        		'choices' => $this->getNettoyages(),
        		'expanded' => false,
        		'multiple' => true,
        		'required' => false,
        		'attr' => array("class" => "phoenix", "multiple" => "multiple", "data-icon"=>"grid", "data-iconpos"=>"left" ),
        ));
        //$builder->get('nettoyages')->resetViewTransformers();

        $builder->add('applications', ChoiceType::class, array(
        		'label' => 'Respect des applications : ',
        		'choices' => $this->getApplications(),
        		'expanded' => false,
        		'multiple' => true,
        		'required' => false,
        		'attr' => array("class" => "phoenix", "multiple" => "multiple", "data-icon"=>"grid", "data-iconpos"=>"left" ),
        ));
      //  $builder->get('applications')->resetViewTransformers();

        $builder->add('emailTransmission', TextType::class, array('label' => 'Email :', 'attr' => array('class' => " phoenix")));
        $builder->add('nomTransmission', TextType::class, array('label' => 'Nom :', 'attr' => array('class' => " phoenix")));
        $builder->add('signatureBase64', TextType::class, array('label' => 'Signature :', 'attr' => array('class' => " phoenix")));
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
        return 'passage_mobile_'.str_replace("-","_",$this->passageId);
    }

    public function getTechniciens() {
        return $this->dm->getRepository('AppBundle:Compte')->findAllUtilisateursTechnicien();
    }

    public function getNettoyages() {
    	$tags = $this->dm->getRepository('AppBundle:Passage')->findAllNettoyages();
    	$result = array();
    	foreach ($tags as $tag) {
    		$result[$tag] = $tag;
    	}
    	return $result;
    }

    public function getApplications() {
    	$tags = $this->dm->getRepository('AppBundle:Passage')->findAllApplications();
    	$result = array();
    	foreach ($tags as $tag) {
    		$result[$tag] = $tag;
    	}
    	return $result;
    }
}
