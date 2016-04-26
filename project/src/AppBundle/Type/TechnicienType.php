<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TechnicienChoiceType
 *
 * @author mathurin
 */

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ODM\MongoDB\DocumentManager as DocumentManager;
use AppBundle\Document\Compte;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;

class TechnicienType extends AbstractType {
	
	
	public function __construct(DocumentManager $documentManager) 
	{
		$this->dm = $documentManager;
	}

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('technicien', DocumentType::class, array(
            "choices" => array_merge(array('' => ''), $this->getComptes(Compte::TYPE_TECHNICIEN)),
            'label' => 'Technicien :',
            'class' => 'AppBundle\Document\Compte',
            'expanded' => false,
            'multiple' => false,
            "attr" => array("class" => "select2 select2-simple")));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'technicien';
    }
    
    public function getComptes($type) {
        return $this->dm->getRepository('AppBundle:Compte')->findAllByType($type);
    }

}
