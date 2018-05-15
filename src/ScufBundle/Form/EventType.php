<?php

namespace ScufBundle\Form;

use ScufBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('all_day', CheckboxType::class)
            ->add('start', DateTimeType::class, [
                'format'  => 'yyyy-MM-dd HH:mm:ss',
                'widget' => 'single_text',
            ])
            ->add('end', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd HH:mm:ss'
            ])
            ->add('location', TextType::class)
            ->add('background_color', TextType::class)
            ->add('border_color', TextType::class)
            ->add('user', EntityType::class, array('class' => User::class))
            ->add('validation', IntegerType::class)
            ->add('confirm', CheckboxType::class)
            ->add('partial_start', DateTimeType::class)
            ->add('partial_end', DateTimeType::class)
            ->add('justification', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ScufBundle\Entity\Event',
            'csrf_protection' => false,
        ));
    }

}
