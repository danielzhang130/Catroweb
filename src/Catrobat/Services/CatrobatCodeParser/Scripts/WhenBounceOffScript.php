<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class WhenBounceOffScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_BOUNCE_OFF_SCRIPT;
    $this->caption = 'When you bounce off';
    $this->setImgFile(Constants::MOTION_SCRIPT_IMG);
  }
}
