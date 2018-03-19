<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;



class AttachementType extends AbstractType {

    protected $dm;
    protected $visibleTechnicienOption;

    public function __construct(DocumentManager $documentManager, $visibleTechnicienOption = true) {
        $this->dm = $documentManager;
        $this->visibleTechnicienOption = $visibleTechnicienOption;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('imageFile', VichFileType::class, array(
            'required' => false,
            'allow_delete' => false,
            'label' => 'Choisir un document (.jpg, .png, .pdf)',
          ))
          ->add('titre', TextType::class, array('label' => 'Nom* :'));
          if($this->visibleTechnicienOption){
              $builder->add('visibleTechnicien', CheckboxType::class, array('label' => ' ', 'required' => false, "attr" => array("class" => "switcher", "data-size" => "mini")));
          }
    }

    /**
     * @return string
     */
    public function getName() {
        return 'attachement';
    }

}
