<?php

namespace ScufBundle\Form\Action;

use ScufBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LeaveType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', TextType::class)
            ->add('created', DateTimeType::class)
            ->add('updated', DateTimeType::class)
            ->add('start', DateType::class)
            ->add('end', DateType::class)
            ->add('status', IntegerType::class)
            ->add('view', IntegerType::class)
            ->add('user', EntityType::class, array('class' => User::class))
            ->add('recipient', EntityType::class, array('class' => User::class))
            ->add('justification', TextType::class)
    ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ScufBundle\Entity\Action',
            'csrf_protection' => false,
        ));
    }

}
