<?php
namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\CallbackTransformer;
use AppBundle\Document\Passage;
use AppBundle\Manager\PassageManager;

class NiveauInfestationPassageMobileType extends AbstractType {

	protected $dm;

	public function __construct(DocumentManager $documentManager)
	{
		$this->dm = $documentManager;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
		->add('identifiant', ChoiceType::class, array('label' => ' ', 'choices'  => array_merge(array('' => ''), $this->getPrestations()), "attr" => array("class" => "form-control phoenix","placeholder" => 'Choisir une prestation')))
		->add('infestation', ChoiceType::class, array('label' => ' ', 'choices'  => array_merge(array('' => ''), $this->getInfestations()), "attr" => array("class" => "form-control phoenix","placeholder" => 'Choisir une infestation')))

		;

		$builder->get('identifiant')
                ->addModelTransformer(new CallbackTransformer(
                        function ($originalDescription) {
                    return (!$originalDescription)? null : $originalDescription;
                }, function ($submittedDescription) {
                    return (!$submittedDescription)? 0 : $submittedDescription;
                }));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
				'data_class' => 'AppBundle\Document\NiveauInfestation',
		));
	}

  public function getPrestations()
  {
    return $this->dm->getRepository('AppBundle:Configuration')->findConfiguration()->getPrestationsArray();
  }

	public function getInfestations()
	{
		return PassageManager::$typesInfestationLibelles;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'niveau_infestation';
	}
}
