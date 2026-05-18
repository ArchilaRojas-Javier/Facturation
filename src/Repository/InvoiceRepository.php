<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    // src/Repository/InvoiceRepository.php
    public function getNextInvoiceNumber(\DateTimeInterface $date): string
    {
        $datePrefix = $date->format('Ymd');
        $pattern = 'FACT-' . $datePrefix . '-%';

        $maxNumber = $this->createQueryBuilder('i')
            ->select('MAX(i.invoiceNumber)')
            ->where('i.invoiceNumber LIKE :pattern')
            ->setParameter('pattern', $pattern)
            ->getQuery()
            ->getSingleScalarResult();

        if ($maxNumber) {
            
            $parts = explode('-', $maxNumber);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        
        return sprintf('FACT-%s-%s', $datePrefix, str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT));
    }

//    /**
//     * @return Invoice[] Returns an array of Invoice objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Invoice
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
