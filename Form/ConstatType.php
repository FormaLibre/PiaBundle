<?php

namespace FormaLibre\PiaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConstatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'content',
            'tinymce',
            array('required' => true, 'label' => false)
        );
    }

    public function getName()
    {
        return 'constat_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }
}
