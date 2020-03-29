<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class DroneEmergencyBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_EMERGENCY_BRICK;
    $this->caption = 'Emergency';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
