<?php

namespace App\Admin\Translation\Controller;

class ProjectMachineTranslationAdminController extends MachineTranslationAdminController
{
  public function __construct()
  {
    parent::__construct(self::TYPE_PROJECT);
  }
}
