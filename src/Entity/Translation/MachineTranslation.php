<?php

namespace App\Entity\Translation;

use App\Utils\TimeUtils;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

abstract class MachineTranslation
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  protected ?int $id = null;

  /**
   * @ORM\Column(type="string", length=5)
   */
  protected string $source_language;

  /**
   * @ORM\Column(type="string", length=5)
   */
  protected string $target_language;

  /**
   * @ORM\Column(type="string")
   */
  protected string $provider;

  /**
   * @ORM\Column(type="integer")
   */
  protected int $usage_count;

  /**
   * @ORM\Column(type="datetime")
   */
  protected DateTime $last_modified_at;

  /**
   * @ORM\Column(type="datetime")
   */
  protected DateTime $created_at;

  public function __construct(string $source_language, string $target_language, string $provider, int $usage_count = 1)
  {
    if (5 < strlen($source_language) || 5 < strlen($target_language)) {
      throw new InvalidArgumentException();
    }

    $this->source_language = $source_language;
    $this->target_language = $target_language;
    $this->provider = $provider;
    $this->usage_count = $usage_count;
  }

  public function incrementCount(): void
  {
    ++$this->usage_count;
  }

  public function getSourceLanguage(): string
  {
    return $this->source_language;
  }

  public function getTargetLanguage(): string
  {
    return $this->target_language;
  }

  public function getProvider(): string
  {
    return $this->provider;
  }

  public function getUsageCount(): int
  {
    return $this->usage_count;
  }

  /**
   * @ORM\PrePersist
   *
   * @throws Exception
   */
  public function initTimestamps(): void
  {
    $this->last_modified_at = TimeUtils::getDateTime();
    $this->created_at = $this->last_modified_at;
  }

  /**
   * @ORM\PreUpdate
   *
   * @throws Exception
   */
  public function updateLastModifiedTimestamp(): void
  {
    $this->last_modified_at = TimeUtils::getDateTime();
  }
}
