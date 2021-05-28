<?php

namespace App\Form;
use App\Entity\User;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\StringType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;


class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', StringType::class, [
                'type' => [
                    'string' => 'firstName', 
                    
                ],
                'required' => true,
            ])
            ->add('lastName', StringType::class, [
                'type' => [
                    'string' => 'lastName', 
                    
                ],
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'type' => [
                    'email' => 'email', 
                    
                ],
                'required' => true,
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Membre' => 'ROLE_USER', 
                    'Coach' => 'ROLE_COACH'
                ],
                'required' => true,
            ])
            
            ->add('password', PasswordType::class, [
                'type' => [
                    'password' => 'password', 
                    
                ],
                'required' => true,
            ])
            ->add('confirm_password')
            // ->add('created_at')
            ->add('age', IntegerType::class, [
                'type' => [
                    'integer' => 'age', 
                    
                ],
                'required' => true,
            ])
            ->add('city', StringType::class, [
                'type' => [
                    'string' => 'city', 
                    
                ],
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'type' => [
                    'text' => 'description', 
                    
                ],
                'required' => true,
            ])
            ->add('sport', TextType::class, [
                'type' => [
                    'text' => 'sport', 
                    
                ],
                'required' => true,
            ])
            ->add('experience', TextType::class, [
                'type' => [
                    'text' => 'experience', 
                    
                ],
                'required' => true,
            ])
            ->add('education', TextType::class, [
                'type' => [
                    'text' => 'experience', 
                    
                ],
                'required' => true,
            ])
            ->add('tarif', IntegerType::class, [
                'type' => [
                    'integer' => 'tarif', 
                    
                ],
                'required' => true,
            ])
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
