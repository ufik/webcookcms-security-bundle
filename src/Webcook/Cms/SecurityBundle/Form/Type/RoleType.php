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

/**
 * Role form type.
 */
class RoleType extends AbstractType
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
                    new NotBlank(array( 'message' => 'security.roles.form.name.required')),
                ),
                'label' => 'security.roles.form.name',
            ))
            ->add('role', 'text', array(
                'constraints' => array(
                    new NotBlank(array( 'message' => 'security.roles.form.rolename.required')),
                ),
                'label' => 'security.roles.form.role',
            ))->add('version', 'hidden', array('mapped' => false));
    }

    /**
     * {@inheritdoc}
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Webcook\Cms\SecurityBundle\Entity\Role',
            'csrf_protection'   => false,
        ));
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'role';
    }
}
