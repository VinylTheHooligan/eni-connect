<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Outing;
use App\Entity\Place;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie',
            ])
            ->add('startDateTime', DateTimeType::class, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
            ])
            ->add('registrationDeadline', DateType::class, [
                'label' => "Date limite d'inscription",
                'widget' => 'single_text',
            ])
            ->add('maxRegistrations', IntegerType::class, [
                'label' => 'Nombre de places',
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (en minutes)',
            ])
            ->add('eventInfo', TextareaType::class, [
                'label' => 'Description et infos',
                'required' => false,
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'label' => 'Campus',
            ])
            ->add('place', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'name',
                'label' => 'Lieu',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Outing::class,
        ]);
    }
}