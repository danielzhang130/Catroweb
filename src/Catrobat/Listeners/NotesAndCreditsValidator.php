<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\Upload\NotesAndCreditsTooLongException;
use App\Catrobat\Services\ExtractedCatrobatFile;

class NotesAndCreditsValidator
{
  private int $max_notes_and_credits_size;

  public function __construct()
  {
    $this->max_notes_and_credits_size = 3_000;
  }

  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    if (strlen($file->getNotesAndCredits()) > $this->max_notes_and_credits_size) {
      throw new NotesAndCreditsTooLongException();
    }
  }
}
