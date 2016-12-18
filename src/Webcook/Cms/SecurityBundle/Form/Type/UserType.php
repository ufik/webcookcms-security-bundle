<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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
            ->add('username', TextType::class, array(
                'constraints' => array(
                    new NotBlank(array('message' => 'security.user.form.name.required')),
                ),
                'label' => 'security.users.form.username',
            ))
            ->add('email', EmailType::class, array(
                'constraints' => array(
                    new Email(array('message' => 'security.user.form.name.not_valid')),
                ),
                'label' => 'security.users.form.email',
            ))
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => false,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
                'label' => 'security.users.form.password',
            ))
            ->add('isActive', CheckboxType::class, array(
                'required' => false,
                'label' => 'security.users.form.is_active',
            ))
            ->add('roles', EntityType::class, array(
                'expanded' => true,
                'multiple' => true,
                'label' => 'security.users.form.roles',
                'class' => 'Webcook\Cms\SecurityBundle\Entity\Role',
            ))->add('version', HiddenType::class, array('mapped' => false));
    }

    /**
     * {@inheritdoc}
     *
     * @param OptionsResolverI $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => \Webcook\Cms\SecurityBundle\Entity\User::class,
            'csrf_protection'   => false,
        ));
    }

    /**
     *  {@inheritdoc}
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'user';
    }
}
