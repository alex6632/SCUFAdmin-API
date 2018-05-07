<?php

namespace ScufBundle\Form;

use ScufBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', TextType::class)
            ->add('created', DateTimeType::class, [
                'model_timezone' => 'UTC',
                'view_timezone'  => 'Europe/Paris'
            ])
            ->add('updated', DateTimeType::class, [
                'model_timezone' => 'UTC',
                'view_timezone'  => 'Europe/Paris'
            ])
            ->add('start', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm',
                //'format' => 'yyyy-MM-dd HH:mm:ss',
            ])
            ->add('end', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm',
                //'format' => 'yyyy-MM-dd HH:mm:ss',
            ])
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
