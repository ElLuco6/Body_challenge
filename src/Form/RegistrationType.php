<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Mime\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options, ClassMetadata $metadata)
    {
        $builder
            ->add('firstName', new Assert\Type('string'))
            ->add('lastName', new Assert\Type('string'))
            ->add('email', Address::class)
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Membre' => 'ROLE_USER', 
                    'Coach' => 'ROLE_COACH'
                ],
                'required' => true,
            ])
            
            ->add('password', PasswordType::class)
            ->add('confirm_password', PasswordType::class)
            // ->add('created_at')
            ->add('age', new Assert\Type([
                'type' => 'integer',
                'message' => 'l {{ Age }} entré doit etre un nombre.',
            ]);
            ->add('city')
            ->add('description')
            ->add('sport')
            ->add('experience')
            ->add('education')
            ->add('tarif')
            ->add('picture', FileType::class, [
                'mapped'     => false, // Evite qu'il vérifie que ça existe en base
                'constraints' => [
                    new File([
                        'maxSize'   => '1024k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Format d\'image non valide (png, jpg ou gif)',
                    ])
                ],
                'required' => false,
            ])
        ;

        // Data transformer
        $builder->get('roles')->addModelTransformer(new CallbackTransformer(
            function ($rolesArray) {
                // transform the array to a string
                return count($rolesArray)? $rolesArray[0]: null;
            },
            function ($rolesString) {
                // transform the string back to an array
                return [$rolesString];
            }
        ));

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
