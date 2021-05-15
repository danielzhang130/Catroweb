<?php

namespace App\Repository;

use App\Entity\Program;
use App\Entity\ProgramTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

class ProgramTranslationRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, ProgramTranslation::class);
  }

  /**
   * @throws ORMException
   */
  public function addNameTranslation(Program $program, string $language, string $text): void
  {
    $qb = $this->createQueryBuilder('t');

    $result = $qb->select('t')
      ->where($qb->expr()->eq('t.program', ":program"))
      ->andWhere($qb->expr()->eq('t.language', ':language'))
      ->setParameter(':program', $program)
      ->setParameter(':language', $language)
      ->distinct()
      ->getQuery()
      ->getResult();

    if (sizeof($result) != 1) {
      $translation = new ProgramTranslation($program, $language, $text);
      $this->getEntityManager()->persist($translation);
      $this->getEntityManager()->flush();
    } else {
      $qb->update()
        ->set('t.name', ':name')
        ->where($qb->expr()->eq('t.program', ":program"))
        ->andWhere($qb->expr()->eq('t.language', ':language'))
        ->setParameter(':program', $program)
        ->setParameter(':language', $language)
        ->setParameter(':name', $text)
        ->getQuery()
        ->execute();
    }
  }

  public function addDescriptionTranslation(): void
  {

  }

  public function addCreditTranslation(): void
  {

  }

  public function getNameTranslation(Program $program, string $language): ?string
  {
    $qb = $this->createQueryBuilder('t');

    $result = $qb->select('t.name')
      ->where($qb->expr()->eq('t.program', ":program"))
      ->andWhere($qb->expr()->eq('t.language', ':language'))
      ->setParameter(':program', $program)
      ->setParameter(':language', $language)
      ->distinct()
      ->getQuery()
      ->getResult();

    return sizeof($result) < 1 ? null : $result[0]['name'];
  }
}
