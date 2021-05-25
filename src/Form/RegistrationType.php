<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('email')
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
            ->add('age')
            ->add('city')
            ->add('description')
            ->add('sport')
            ->add('experience')
            ->add('education')
            ->add('tarif')
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
