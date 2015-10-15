<?php

namespace FormaLibre\PiaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Repository\UserRepository;

class ActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('label' => 'Nom'))
            ->add('description', 'tinymce', array('required' => false, 'label' => 'Commentaire'))
            ->add('intervenantPossible', 'entity', array(
                'label' => 'Intervenants possible',
                'expanded' => false,
                'multiple' => true,
                'class' => 'Claroline\CoreBundle\Entity\User',
                'query_builder' => function (UserRepository $userRepository) {
                        $query = $userRepository->createQueryBuilder('u')
                            ->join('u.groups', 'g', 'WITH', 'g.name = \'ProfD1\'');
                        return $query;
                    },
                'property' => 'username'
            ))
            ->add('save', 'submit', array('label'=>'Créer'))
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FormaLibre\PiaBundle\Entity\Actions'
        ));
    }

    public function getName()
    {
        return 'ActionForm';
    }

}