<?php

namespace FormaLibre\PiaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Repository\UserRepository;

class TacheType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('action')
            ->add('titre', 'text', array('label' => 'titre'))
            ->add('commentaire', 'tinymce', array('required' => false, 'label' => 'Commentaire'))
            ->add('responsable', 'entity', array(
                'label' => 'Responsable',
                'class' => 'Claroline\CoreBundle\Entity\User',
                'query_builder' => function (UserRepository $userRepository) {
                    $query = $userRepository->createQueryBuilder('u')
                        ->join('u.roles', 'r', 'WITH', 'r.name = \'ROLE_PIA\'')
                        ->orderBy('u.lastName, u.firstName', 'ASC');
                    return $query;
                },
                'placeholder' => '--- Veuillez choisir un responsable ---',
                'empty_data'  => null,
                'required' => 'false'
            ))
            ->add('fini', 'checkbox', array('label'=>'Terminée'))
            ->add('priorite', 'number', array('label'=>'Priorite'))
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\PiaBundle\Entity\Taches'
        ));
    }

    public function getName()
    {
        return 'TacheForm';
    }

}