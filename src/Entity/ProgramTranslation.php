<?php


namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProgramTranslationRepository")
 * @ORM\Table(name="program_translation")
 */
class ProgramTranslation
{
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   */
  private ?int $id = null;

  /**
   * @ORM\ManyToOne(
   *     targetEntity="App\Entity\Program",
   *     inversedBy="translations"
   * )
   * @ORM\JoinColumn(name="program_id", referencedColumnName="id")
   */
  private Program $program;

  /**
   * @ORM\Column(type="string")
   */
  private string $language;

  /**
   * @ORM\Column(type="string", length=300, nullable=true)
   */
  private ?string $name;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private ?string $description;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  private ?string $credits;

  /**
   * ProgramTranslation constructor.
   * @param Program $program
   * @param string $language
   * @param string|null $name
   * @param string|null $description
   * @param string|null $credits
   */
  public function __construct(Program $program, string $language, ?string $name = null, ?string $description = null, ?string $credits = null)
  {
    $this->program = $program;
    $this->language = $language;
    $this->name = $name;
    $this->description = $description;
    $this->credits = $credits;
  }
}