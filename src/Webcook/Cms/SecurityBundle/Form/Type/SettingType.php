<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

/**
 * User form type.
 */
class SettingType extends AbstractType
{
    /**
     * {@inheritdoc}
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'constraints' => array(
                    new NotBlank(array( 'message' => 'security.setting.form.name.required')),
                ),
                'label' => 'security.settings.form.name',
            ))
            ->add('key', 'text', array(
                'constraints' => array(
                    new NotBlank(array( 'message' => 'security.setting.form.key.required')),
                ),
                'label' => 'security.settings.form.email',
            ))
            ->add('value', 'text', array(
                'constraints' => array(
                    new NotBlank(array( 'message' => 'security.setting.form.name.required')),
                ),
                'label' => 'security.settings.form.password',
            ))
            ->add('section', 'text', array(
                'constraints' => array(
                    new NotBlank(array( 'message' => 'security.setting.form.name.required')),
                ),
                'label' => 'security.settings.form.section',
            ));
          
    }

    /**
     * {@inheritdoc}
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Webcook\Cms\SecurityBundle\Entity\Setting',
            'csrf_protection'   => false,
        ));
    }

    /**
     *  {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'setting';
    }
}
