<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Type\Adresse;
use AppBundle\Type\ContactCoordonnee;
use AppBundle\Manager\EtablissementManager;
use AppBundle\Transformer\ProvenanceTransformer;

class SocieteType extends AbstractType {

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
                ->add('raisonSociale', TextType::class, array('label' => 'Raison sociale :'))
                ->add('codeComptable', TextType::class, array('label' => 'Code comptable :'))
                ->add('commentaire', TextareaType::class, array('label' => 'Commentaires :', "attr" => array("class" => "form-control", "rows" => 6)))
                ->add('save', SubmitType::class, array('label' => 'Enregistrer', "attr" => array("class" => "btn btn-success pull-right")))
        		->add('adresse', AdresseType::class, array('data_class' => 'AppBundle\Document\Adresse'))
        		->add('contactCoordonnee', ContactCoordonneeType::class, array('data_class' => 'AppBundle\Document\ContactCoordonnee'));
        
       
        
        $builder->add('provenance', ChoiceType::class, array(
            		'choices' => array_merge(array('' => ''), $this->getProvenances()),
	        		'expanded' => false, 
	        		'multiple' => false,
        			'attr' => array("class" => "select2 select2-simple"),
        ));
        $builder->get('provenance')->addModelTransformer(new ProvenanceTransformer($this->dm));
        
        $builder->add('tags', ChoiceType::class, array(
        		'choices' => $this->getTags(),
        		'expanded' => false,
        		'multiple' => true,
        		'attr' => array("class" => "select2 select2-simple", "data-tags" => "true"),
        ));
        $builder->get('tags')->resetViewTransformers();
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Societe',
        ));
    }
    
    public function getProvenances() 
    {
    	return $this->dm->getRepository('AppBundle:Configuration')->findConfiguration()->getProvenances()->toArray();
    }
    
    public function getTags() 
    {
    	$tags = $this->dm->getRepository('AppBundle:Societe')->findAllTags();
    	$result = array();
    	foreach ($tags as $tag) {
    		$result[$tag] = $tag;
    	}
    	return $result;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'societe';
    }

    public function getTypes() {
        return EtablissementManager::$type_libelles;
    }

}
