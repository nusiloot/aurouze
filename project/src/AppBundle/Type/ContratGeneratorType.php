<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Manager\ContratManager;

class ContratGeneratorType extends AbstractType {
	
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('moyens', ChoiceType::class, array('expanded' => true, 'multiple' => true, 'choices' => $this->getChoices(), 'label' => 'Moyens de mise en oeuvre', "attr" => array("class" => ""), 'required' => false))
				->add('conditionsParticulieres', TextareaType::class, array('label' => 'Conditions particulières :', "attr" => array("class" => "form-control", "rows" => 5), 'required' => false))
		        ->add('save', SubmitType::class, array('label' => 'Générer', "attr" => array("class" => "btn btn-success pull-right")));
	}

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Contrat',
        ));
    }

    public function getChoices()
    {
    	return ContratManager::$moyens_contrat_libelles;
    }

	/**
	 * @return string
	 */
	public function getName() {
		return 'contrat_generator';
	}
	
}