<?php

namespace App\Twig\Components;

use App\Repository\ClientRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use App\Entity\Client;
use App\Form\ClientType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER')]

#[AsLiveComponent]
final class NewClient
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true, fieldName: 'clientForm')]
    public ?Client $client = null;

    public function __construct(
        private ClientRepository $clientRepository,
        private EntityManagerInterface $entityManagerInterface,
        private FormFactoryInterface $formFactoryInterface,
        private Security $security
    ) {}

    protected function instantiateForm(): \Symfony\Component\Form\FormInterface
    {
        $client = $this->client ?? new Client();
        return $this->formFactoryInterface->create(ClientType::class, $client);
    }

    #[LiveAction]
    public function saveClient(): void
    {
        $this->submitForm();
        $user = $this->security->getUser();
        $form = $this->getForm();
        if ($form->isValid()) {
            /** @var Client $client */
            $client = $form->getData();
            $client->setUser($user);
            $this->entityManagerInterface->persist($client);
            $this->entityManagerInterface->flush();

            $this->client = new Client();
            $this->resetForm();
        }
    }
    public function getAllClients(): array
    {
        return $this->clientRepository->findby(['user' => $this->security->getUser()]);
    }
}
