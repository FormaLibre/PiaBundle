<?php

namespace FormaLibre\PiaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Repository\UserRepository;

class SuiviType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attr = array();
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = 'dd-mm-yyyy';
        $attr['autocomplete'] = 'off';

        $builder
            ->add('date',
                'datepicker',
                array(
                    'label' => 'Date',
                    'required'  => true,
                    'widget'    => 'single_text',
                    'format'    => 'dd-MM-yyyy',
                    'attr'      => $attr,
                    'autoclose' => true
                ))
            ->add('description', 'text', array('required' => false, 'label' => 'Commentaire'))
            ->add('intervenant', 'entity', array(
                'label' => 'Intervenant',
                'class' => 'Claroline\CoreBundle\Entity\User',
                'query_builder' => function (UserRepository $userRepository) {
                    $query = $userRepository->createQueryBuilder('u')
                        ->join('u.roles', 'r', 'WITH', 'r.name = \'ROLE_PROF\'')
                        ->orderBy('u.lastName, u.firstName', 'ASC');
                    return $query;
                },
                'placeholder' => '--- Veuillez choisir un intervenant ---',
                'empty_data'  => null,
                'required' => 'false'
            ))
            ->add('save', 'submit', array('label'=>'Ajouter','attr' => array('class' => 'btn btn-primary addSuivi')));
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\PiaBundle\Entity\Suivis'
        ));
    }

    public function getName()
    {
        return 'SuiviForm';
    }

}