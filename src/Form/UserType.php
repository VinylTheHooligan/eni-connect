<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


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
                'attr' => ['accept' => '.jpg,.jpeg,.png'],
                'constraints' => [
                    new File([
                        'extensions' => ['jpg', 'jpeg', 'png'],
                        'extensionsMessage' => 'Veuillez uploader une image JPG ou PNG uniquement.',
                    ])
                ]
            ])
        ;

        if ($options['include_roles'] ?? false) {
            $builder->add('mainRole', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Organisateur' => 'ROLE_ORGANIZER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'expanded' => true,
                'multiple' => false,
                'mapped' => false,
                'data' => $options['default_role'] ?? 'ROLE_USER',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'include_roles' => false,
            'default_role' => 'ROLE_USER',
        ]);
    }
}
