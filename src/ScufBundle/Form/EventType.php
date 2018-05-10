<?php

namespace ScufBundle\Form;

use Symfony\Component\Form\AbstractType;
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
            ->add('all_day', IntegerType::class)
            ->add('start', DateTimeType::class, [
                'widget' => 'single_text',
                'format'  => 'dd-MM-yyyy HH:mm'
            ])
            ->add('end', DateTimeType::class, [
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy HH:mm'
            ])
            ->add('location', TextType::class)
            ->add('bg_color', TextType::class)
            ->add('border_color', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ScufBundle\Entity\Event',
            'csrf_protection' => false,
        ));
    }

}
