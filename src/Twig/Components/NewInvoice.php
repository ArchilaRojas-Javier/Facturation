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
use App\Enums\StatusEnum;
use App\Form\InvoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


#[IsGranted('ROLE_USER')]

#[AsLiveComponent]
final class NewInvoice extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp(writable: true, fieldName: 'invoiceForm')]
    public Invoice $invoice;

    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private EntityManagerInterface $entityManagerInterface,
        private FormFactoryInterface $formFactoryInterface,
        private Security $security,
        private ProductRepository $productRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {
        $this->invoice = new Invoice();
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
    public function saveInvoice(): mixed
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

        $this->submitForm();
        $user = $this->security->getUser();
        $form = $this->getForm();
        if ($form->isValid()) {
            /** @var Invoice $invoice */
            $invoice = $form->getData();
            $invoice->setUser($user);
            $invoice->setTotalTtc($total);

            $issuedDate = new \DateTimeImmutable();
            $invoiceNumber = $this->invoiceRepository->getNextInvoiceNumber($issuedDate);
            $invoice->setNumber($invoiceNumber);

            // ¿Qué botón se ha pulsado?
            $isDraft = $form->get('saveDraft');
            $isRegister = $form->get('register');

            if ($isDraft) {
                // Guardar borrador sin importar si hay errores de validación
                $invoice->setStatus(StatusEnum::DRAFT);
                $this->entityManagerInterface->persist($invoice);
                $this->entityManagerInterface->flush();

                // Limpieza y redirección
                $this->invoice = new Invoice();
                $this->tempInvoiceItems = [];
                $this->resetForm();
                return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
            }
            // Registro oficial → solo si el formulario es completamente válido
            if ($form->isValid()) {
                $invoice->setStatus(StatusEnum::PENDINGPAYMENT);
                $this->entityManagerInterface->persist($invoice);
                $this->entityManagerInterface->flush();

                // Limpieza y redirección
                $this->invoice = new Invoice();
                $this->tempInvoiceItems = [];
                $this->resetForm();
                return $this->redirectToRoute('app_invoice_index', [], Response::HTTP_SEE_OTHER);
            }

        }
        return null;
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

// public function deleteInvoice(Invoice $invoice): Response
// {
//     // No se pueden borrar facturas que no sean borrador
//     if ($invoice->getStatus() !== InvoiceStatus::Draft) {
//         $this->addFlash('error', 'Solo las facturas en borrador pueden eliminarse.');
//         return $this->redirectToRoute('app_invoice_show', ['id' => $invoice->getId()]);
//     }

//     $this->entityManagerInterface->remove($invoice);
//     $this->entityManagerInterface->flush();

//     return $this->redirectToRoute('app_invoice_index');
// }