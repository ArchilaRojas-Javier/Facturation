<?php

namespace App\Twig\Components;

use App\Entity\InvoiceItem;
use App\Repository\InvoiceRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use App\Entity\Invoice;
use App\Entity\Product;
use App\Form\InvoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER')]

#[AsLiveComponent]
final class NewInvoice 
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true, fieldName : 'invoiceForm')]
    public Invoice $invoice;

    public Product $currentNewProduct;

    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private EntityManagerInterface $entityManagerInterface,
        private FormFactoryInterface $formFactoryInterface,
        private Security $security
    ) {
        $this->invoice = new Invoice();
        $this->currentNewProduct = new Product();
    }

    // #[LiveAction]
    // public function addProduct(){
    //     $invoiceItem = new InvoiceItem();
    //     $invoiceItem->addProduct($this->currentNewProduct);
    //     $this->invoice->addInvoiceItem($invoiceItem);
    // }

    protected function instantiateForm(): \Symfony\Component\Form\FormInterface
    {
        return $this->formFactoryInterface->create(InvoiceType::class, $this->invoice);
    }

    #[LiveAction]
    public function saveInvoice(): void
    {
        $this->submitForm();
        $user = $this->security->getUser();
        $form = $this->getForm();
        if ($form->isValid()) {
            /** @var Invoice $invoice */
            $invoice = $form->getData();
            $invoice->setUser($user);
            $this->entityManagerInterface->persist($invoice);
            $this->entityManagerInterface->flush();

            $this->invoice = new Invoice();
            $this->resetForm();
        }
    }
    public function getAllInvoices(): array
    {
        return $this->invoiceRepository->findby(['user' => $this->security->getUser()]);
    }

        #[LiveProp(writable: true)]
    public bool $showProductForm = false;

    #[LiveAction]
    public function toggleProductForm(): void
    {
        $this->showProductForm = !$this->showProductForm;
    }

    #[LiveListener('toggleProductForm')]
    public function onProductCreationCancelled(): void
    {
        $this->showProductForm = false;
    }
}

