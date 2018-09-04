<?php

namespace ScufBundle\Form;

use ScufBundle\Entity\Setting;
use ScufBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WeekType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', IntegerType::class)
            ->add('to', IntegerType::class)
            ->add('hours_done', NumberType::class)
            ->add('hours', NumberType::class)
            ->add('user', EntityType::class, array('class' => User::class));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ScufBundle\Entity\Week',
            'csrf_protection' => false,
        ));
    }

}
