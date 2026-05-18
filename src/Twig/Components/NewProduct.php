<?php

namespace App\Twig\Components;

use App\Repository\ProductRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\LiveComponentTrait;
use Symfony\UX\LiveComponent\ComponentToolsTrait;

use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER')]

#[AsLiveComponent]    
final class NewProduct
{
    // use LiveComponentTrait;
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ComponentToolsTrait;

    #[LiveProp(writable: true, fieldName: 'productForm')]
    public ?Product $product = null;

    #[LiveProp(writable: true)]
    public bool $showList = true; 

    #[LiveAction]
    public function cancelCreation(): void
    {
    // Emite un evento hacia el componente padre (si existe)
        $this->emitUp('toggleProductForm');
    }

    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManagerInterface,
        private FormFactoryInterface $formFactoryInterface,
        private Security $security
    ) {}

    protected function instantiateForm(): \Symfony\Component\Form\FormInterface
    {
        $product = $this->product ?? new Product();
        return $this->formFactoryInterface->create(ProductType::class, $product);
    }

    #[LiveAction]
    public function saveProduct(): void
    {

        $this->submitForm();
        $user = $this->security->getUser();

        $form = $this->getForm();
        if ($form->isValid()) {
            /** @var Product $product */
            $product = $form->getData();
            $product->setUser($user);
            $this->entityManagerInterface->persist($product);
            $this->entityManagerInterface->flush();

            $this->product = new Product();
            $this->resetForm();
        }
    }
    public function getAllProducts(): array
    {
        return $this->productRepository->findby(['user' => $this->security->getUser()]);
    }
}
