<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('email')
            //->add('roles')
            //->add('password')
            //->add('first_name')
            //->add('last_name')
            ->add('company_name', null, [
                'label' => 'Raison sociale',
            ])
            ->add('iban', null, [
                'label' => 'IBAN',
            ])
            ->add('siret', null, [
                'label' => 'Numero SIRET(optionnel)',
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
