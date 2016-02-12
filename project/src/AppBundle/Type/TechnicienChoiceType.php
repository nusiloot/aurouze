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

class TechnicienChoiceType extends AbstractType {
	
	protected $dm;
	
	public function __construct(DocumentManager $documentManager) 
	{
		$this->dm = $documentManager;
	}

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('technicien', 'choice', array("choices" => $this->getChoices(),
            'label' => 'Rechercher un technicien',
            'expanded' => false,
            'multiple' => false,
            "attr" => array("class" => "form-control select2")))

        ;
    }

    /**
     * @return string
     */
    public function getName() {
        return 'technicien_choice';
    }
    
    public function getChoices() {
    	return $this->dm->getRepository('AppBundle:Passage')->findTechniciens();
    }

}
