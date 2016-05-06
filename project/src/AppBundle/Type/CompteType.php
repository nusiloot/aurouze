<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use AppBundle\Type\ContactCoordonneeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use AppBundle\Type\TagType;
use AppBundle\Manager\CompteManager;


class CompteType extends AbstractType {

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
        ->add('civilite', TextType::class, array('label' => 'Civilite* :','required' => false))
        ->add('nom', TextType::class, array('label' => 'Nom* :'))
        ->add('prenom', TextType::class, array('label' => 'Nom* :'))
        ->add('actif', CheckboxType::class, array('label' => 'Actif* :', 'required' => false, 'empty_data' => null))
        ->add('save', SubmitType::class, array('label' => 'Enregistrer', "attr" => array("class" => "btn btn-success pull-right")))
        ->add('adresse', AdresseType::class, array('data_class' => 'AppBundle\Document\Adresse'))
        
        ->add('contactCoordonnee', ContactCoordonneeType::class, array('data_class' => 'AppBundle\Document\ContactCoordonnee'));

        $builder->add('sameContact', CheckboxType::class, array('label' => 'Même contact société', 'required' => false, 'empty_data' => null, "attr" => array("class" => "collapse-checkbox", "data-target" => "#collapseContact")));
        $builder->add('sameAdresse', CheckboxType::class, array('label' => 'Même adresse société', 'required' => false, 'empty_data' => null, "attr" => array("class" => "collapse-checkbox", "data-target" => "#collapseAdresse")));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Compte',
        ));
    }

     public function getTags() {
         
         $result = array();
    	foreach (CompteManager::$tagsCompteLibelles as $key =>  $tag) {
    		$result[$key] = $tag;
    	}
    	return $result;
    }
    
    /**
     * @return string
     */
    public function getName() {
        return 'compte';
    }
}
