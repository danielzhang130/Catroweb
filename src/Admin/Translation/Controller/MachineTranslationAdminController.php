<?php


namespace App\Admin\Translation\Controller;

use App\Commands\Helpers\CommandHelper;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class MachineTranslationAdminController extends CRUDController
{
  const TYPE_PROJECT = 'TYPE_PROJECT';
  const TYPE_COMMENT = 'TYPE_COMMENT';

  private string $type;

  public function __construct(string $type)
  {
    $this->type = $type;
  }

  public function listAction(Request $request = null): Response
  {
    return $this->renderWithExtraParams('Admin/Translation/admin_machine_translation.html.twig', [
      'action' => 'list',
      'trimUrl' => $this->admin->generateUrl('trim')
    ]);
  }

  public function trimAction(KernelInterface $kernel): Response
  {
    $request = $this->getRequest();
    $days = $request->get('days');

    if (1 > $days) {
      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    $entity = $this->type == self::TYPE_PROJECT ? '--only-project' : '--only-comment';

    $result = CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:translation:trim-storage', '--older-than', $days, $entity],
      ['timeout' => 86400], '', null, $kernel
    );

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Command finished successfully');
    } else {
      $this->addFlash('sonata_flash_error', "Error occurred running command!");
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
