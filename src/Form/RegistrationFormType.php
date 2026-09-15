<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email as ConstraintsEmail;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('first_name', TextType::class, [
            'label' => 'Prenom',
            'attr' => [
                'placeholder' => 'Jean',
            ],
        ])
        ->add('last_name', TextType::class, [
            'label' => 'Nom',
            'attr' => [
                'placeholder' => 'Dupont',
            ],
        ])
        ->add('company_name', TextType::class, [
            'label' => 'Raison Sociale',
            'attr' => [
                'placeholder' => 'Tech Solutions',
            ],
        ])
        ->add('iban', TextType::class, [
            'label' => 'IBAN',
            'attr' => [
                'placeholder' => 'FR76 3000 6000 0112 3456 7890 189',
            ],
        ])
        ->add('email', EmailType::class, [
            'label' => 'Adresse Email',
            'attr' => [
                'placeholder' => 'contact@techsolutions.com',
            ],
        ])
        
        
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password',
                'placeholder' => '*******'],
                'constraints' => [
                    new NotBlank(
                        message: 'Please enter a password',
                    ),
                    new Length(
                        min: 6,
                        minMessage: 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        max: 4096,
                    ),
                ],
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
