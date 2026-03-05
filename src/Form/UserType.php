<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Pseudo
            ->add('username', TextType::class, [
                'label' => 'Pseudo'
            ])
            // Prénom
            ->add('firstName', TextType::class, [
                'label' => 'Prénom'
            ])
            // Nom
            ->add('lastName', TextType::class, [
                'label' => 'Nom'
            ])
            // Téléphone optionnel
            ->add('phoneNumber', TextType::class, [
                'label' => 'Téléphone',
                'required' => false
            ])
            // Email
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ])
            // Mot de passe avec confirmation
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => false,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation'],
            ])

            ->add('profilePictureFile', FileType::class, [
                'label' => 'Ma photo',
                'required' => false,
                'mapped' => false, // pas lié directement à l'entité
                'attr' => ['accept' => 'image/*']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
