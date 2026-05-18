<?php

namespace App\Form;

use App\Entity\Invoice;
use App\Entity\Client;
use App\Entity\InvoiceItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\ClientRepository;
use App\Repository\InvoiceItemRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class InvoiceType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();
        $builder
            
            ->add('client', EntityType::class,[
                'class' => Client::class,
                'choice_label' => 'name',
                'query_builder' => function (ClientRepository $cr) use ($user) {
                    return $cr->createQueryBuilder('c')
                        ->where('c.user = :user')
                        ->setParameter('user', $user)
                        ->orderBy('c.name', 'ASC');
                },
            ])
                ->add('invoiceItems', CollectionType::class, [
                'entry_type' => InvoiceItemType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
            ]);
           
            
           
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}

        