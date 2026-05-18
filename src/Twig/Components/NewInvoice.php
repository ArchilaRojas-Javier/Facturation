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
use App\Repository\ProductRepository;
use Symfony\UX\LiveComponent\Attribute\LiveArg;


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
        private Security $security,
        private ProductRepository $productRepository
    ) {
        $this->invoice = new Invoice();
        $this->currentNewProduct = new Product();
    }
     protected function instantiateForm(): \Symfony\Component\Form\FormInterface
    {
        return $this->formFactoryInterface->create(InvoiceType::class, $this->invoice);
    }

    #[LiveProp(writable: true)]
    public ?int $selectedProductId = null;

    #[LiveProp(writable: true)]
    public int $quantity = 1;
    
    #[LiveAction]
    public function saveInvoice(): void
    {
        $total = $this->getTotal();

        foreach ($this->tempInvoiceItems as $itemData) {
            $product = $this->productRepository->find($itemData['productId']);
            if (!$product) continue;

            $invoiceItem = new InvoiceItem();
            $invoiceItem->setProduct($product);
            $invoiceItem->setQuantity($itemData['quantity']);
            $invoiceItem->setUnitPrice($itemData['unitPrice']);
            $invoiceItem->setInvoice($this->invoice);
            $this->invoice->addInvoiceItem($invoiceItem);
        }

        $this->invoice->setTotalTtc($total);
        $this->submitForm();
        $user = $this->security->getUser();
        $form = $this->getForm();
        if ($form->isValid()) {
            /** @var Invoice $invoice */
            $invoice = $form->getData();
            $invoice->setUser($user);
            $invoice->setTotalTtc($total);
            $this->entityManagerInterface->persist($invoice);
            $this->entityManagerInterface->flush();

            $this->invoice = new Invoice();
            $this->tempInvoiceItems = [];
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

    #[LiveProp]
    public array $tempInvoiceItems = [];

    #[LiveAction]
    public function addProductToInvoice(): void
    {
        if (!$this->selectedProductId) {
        return;
        }

        $product = $this->productRepository->find($this->selectedProductId);
        if (!$product) {
        return;
        }
            
        $this->tempInvoiceItems[] = 
        [
        'productId' => $product->getId(),
        'productName' => $product->getName(),
        'quantity' => $this->quantity,
        'unitPrice' => $product->getPrice(),
        ];

        
        $this->selectedProductId = null;
        $this->quantity = 1;
    }

   #[LiveListener('removeTempInvoiceItem')]
    public function removeTempInvoiceItem(#[LiveArg] int $key): void
    {
        unset($this->tempInvoiceItems[$key]);
    }

    public function getAllProducts(): array
    {
        return $this->productRepository->findBy(['user' => $this->security->getUser()]);
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->tempInvoiceItems as $item) {
        $total += $item['quantity'] * $item['unitPrice'];
        }
        return $total;
    }
}

