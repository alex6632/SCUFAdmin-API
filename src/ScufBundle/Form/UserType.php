<?php

namespace ScufBundle\Form;

use ScufBundle\Entity\Access;
use ScufBundle\Entity\Action;
use ScufBundle\Entity\Event;
use ScufBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class)
            ->add('password', PasswordType::class)
            ->add('previous_password', TextType::class)
            ->add('plain_password', TextType::class)
            ->add('confirm_password', TextType::class)
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('hours_todo', IntegerType::class)
            ->add('hours_done', NumberType::class)
            ->add('hours_planified', NumberType::class)
            ->add('hours_planified_by_me', NumberType::class)
            ->add('overtime', NumberType::class)
            ->add('role', IntegerType::class)
            ->add('access', EntityType::class, array(
                'class' => Access::class,
                'choice_label' => function ($access) {
                    return $access->getTitle();
                },
                'multiple' => true
            ))
            ->add('superior', EntityType::class, array(
                'class' => User::class,
                'choice_label' => function ($user) {
                    return $user->getFirstname().' '.$user->getLastname();
                }
            ))
            ->add('event', EntityType::class, array(
                'class' => Event::class,
                'choice_label' => function ($event) {
                    return $event->getTitle();
                },
                'multiple' => true
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ScufBundle\Entity\User',
            'csrf_protection' => false,
        ));
    }

}
