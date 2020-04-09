<?php

namespace AppBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use AppBundle\Document\Compte;


class DevisType extends AbstractType
{

    protected $dm = null;
    protected $cm = null;
    protected $com = null;

    public function __construct($dm, $cm, $commercial) {
        $this->dm = $dm;
        $this->cm = $cm;
        $this->com = $commercial;

    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lignes', CollectionType::class, array(
                'entry_type' => new DevisLigneType($this->cm),
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'label' => '',
            ))
            ->add('description', TextareaType::class, array('label' => 'Informations complémentaires :', 'required' => false, "attr" => array("class" => "form-control", "rows" => 3)))
            ->add('commercial', DocumentType::class, array_merge(array('required' => false), array(
                "choices" => $this->getCommerciaux(),
                'label' => 'Commercial* :',
                'class' => 'AppBundle\Document\Compte',
                'expanded' => false,
                'multiple' => false,
                "attr" => array("class" => "select2 select2-simple"))))
            ->add('datePrevision', DateType::class, array(
                'label' => 'Date du devis',
                "attr" => array(
                    'class' => 'input-inline datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'dd/mm/yyyy'
                ),
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy'
            ))
            ->add('techniciens', DocumentType::class, array(
                'choices' => $this->getParticipants(),
                'class' => Compte::class,
                'expanded' => false,
                'multiple' => true,
                'attr' => array("class" => "select2 select2-simple", "multiple" => "multiple", "style" => "width:100%;")
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Document\Devis'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'devis';
    }

    public function getFrequences() {
        $tags = $this->dm->getRepository('AppBundle:Contrat')->findAllFrequences();
        return array_merge(array(null => null), $tags);
    }

    public function getCommerciaux() {
        return $this->dm->getRepository('AppBundle:Compte')->findAllUtilisateursCommercial();
    }

    public function getDefaultCommercial() {
        return $this->dm->getRepository('AppBundle:Compte')->findOneByIdentifiant($this->com);
    }

    public function getParticipants() {
        return $this->dm->getRepository('AppBundle:Compte')->findAllUtilisateursTechnicien();
    }
}
