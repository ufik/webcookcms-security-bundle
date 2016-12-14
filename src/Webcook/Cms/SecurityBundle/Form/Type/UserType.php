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
class UserType extends AbstractType
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
            ->add('username', 'text', array(
                'constraints' => array(
                    new NotBlank(array( 'message' => 'security.user.form.name.required')),
                ),
                'label' => 'security.users.form.username',
            ))
            ->add('email', 'email', array(
                'constraints' => array(
                    new Email(array( 'message' => 'security.user.form.name.not_valid')),
                ),
                'label' => 'security.users.form.email',
            ))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => false,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
                'label' => 'security.users.form.password',
            ))
            ->add('isActive', 'checkbox', array(
                'required' => false,
                'label' => 'security.users.form.is_active',
            ))
            ->add('roles', 'entity', array(
                'expanded' => true,
                'multiple' => true,
                'label' => 'security.users.form.roles',
                'class' => 'Webcook\Cms\SecurityBundle\Entity\Role',
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
            'data_class' => 'Webcook\Cms\SecurityBundle\Entity\User',
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
        return 'user';
    }
}
