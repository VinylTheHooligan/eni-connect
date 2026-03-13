<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;


class UserImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Fichier CSV',
                'mapped' => false,
                'required' => true,
                'attr' => ['accept' => '.csv,text/csv'],
                'constraints' => [
                    new File([
                        'mimeTypes' => ['text/csv', 'text/plain', 'application/csv'],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier CSV uniquement.',
                    ])
                ]
            ])
            ->add('hasHeader', CheckboxType::class, [
                'label' => 'Le fichier contient une ligne d’en-tête',
                'required' => false,
                'mapped' => false,
                'data' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
