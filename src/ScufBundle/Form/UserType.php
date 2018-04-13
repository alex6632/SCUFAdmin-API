<?php

namespace ScufBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('superior', IntegerType::class)
            ->add('hours_todo', IntegerType::class)
            ->add('hours_done', IntegerType::class)
            ->add('hours_planified', IntegerType::class)
            ->add('hours_planified_by_me', IntegerType::class)
            ->add('overtime', IntegerType::class)
            ->add('username', TextType::class)
            ->add('password', PasswordType::class)
            ->add('role', IntegerType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ScufBundle\Entity\User',
            'csrf_protection' => false,
        ));
    }

}
