<?php

namespace App\Commands\DBUpdater\CronJobs;

use App\Entity\Translation\CommentMachineTranslation;
use App\Entity\Translation\ProjectMachineTranslation;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TranslationTrimStorageCommand extends Command
{
  private const OLDER_THAN = 'older-than';
  protected static $defaultName = 'catrobat:translation:trim-storage';

  private EntityManagerInterface $entity_manager;

  public function __construct(EntityManagerInterface $entity_manager)
  {
    parent::__construct();
    $this->entity_manager = $entity_manager;
  }

  protected function configure(): void
  {
    $this->setName(self::$defaultName)
      ->setDescription('Clean up old db entries in machine translation schema')
      ->addOption(self::OLDER_THAN, null, InputOption::VALUE_REQUIRED,
      'delete entries older than the specified days',
      30)
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $days = $input->getOption(self::OLDER_THAN);

    if ($days < 1) {
      $output->writeln('Specified number of days must be greater than 0');

      return 1;
    }

    $today = new DateTimeImmutable();
    $date = $today->sub(new DateInterval("P{$days}D"));

    $this->deleteEntries(ProjectMachineTranslation::class, $date);
    $this->deleteEntries(CommentMachineTranslation::class, $date);

    return 0;
  }

  private function deleteEntries(string $entity, DateTimeImmutable $older_than_date): void
  {
    $qb = $this->entity_manager->createQueryBuilder();

    $qb->delete($entity, 'e')
      ->where($qb->expr()->lt('e.last_modified_at', ':date'))
      ->setParameter('date', $older_than_date)
      ->getQuery()
      ->execute()
    ;
  }
}
