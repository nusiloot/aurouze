<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class FactureType extends AbstractType
{

    protected $dm = null;
    protected $cm = null;

    public function __construct($dm, $cm) {
        $this->dm = $dm;
        $this->cm = $cm;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateFacturation', DateType::class, array(
            'label' => 'Date de facturation',
            "attr" => array(
                'class' => 'input-inline datepicker',
                'data-provide' => 'datepicker',
                'data-date-format' => 'dd/mm/yyyy'
            ),
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy'))
            ->add('lignes', CollectionType::class, array(
                'entry_type' => new FactureLigneType($this->cm),
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'label' => '',
            ))
            ->add('frequencePaiement', ChoiceType::class, array(
                    'label' => 'Fréquence de paiement',
                    'choices' => $this->getFrequences(),
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'attr' => array("class" => "select2 select2-simple", "data-placeholder" => "Séléctionner une fréquence de paiement"),
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Facture'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'facture';
    }

    public function getFrequences() {
        $tags = $this->dm->getRepository('AppBundle:Contrat')->findAllFrequences();
        return array_merge(array(null => null), $tags);
    }
}
