<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\CatroNotificationService;
use App\Catrobat\StatusCode;
use App\Entity\CommentNotification;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserComment;
use App\Translation\TranslationDelegate;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentsController extends AbstractController
{
  /**
   * @Route("/reportComment", name="report", methods={"GET"})
   *
   * @throws Exception
   */
  public function reportCommentAction(): Response
  {
    $user = $this->getUser();
    if (null === $user) {
      return new Response(StatusCode::NOT_LOGGED_IN);
    }

    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository(UserComment::class)->find($_GET['CommentId']);

    if (null === $comment) {
      throw $this->createNotFoundException('No comment found for this id '.$_GET['CommentId']);
    }

    $comment->setIsReported(true);
    $em->flush();

    return new Response(Response::HTTP_OK);
  }

  /**
   * @Route("/deleteComment", name="delete", methods={"GET"})
   *
   * @throws Exception
   */
  public function deleteCommentAction(): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user) {
      return new Response(StatusCode::NOT_LOGGED_IN);
    }

    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository(UserComment::class)->find($_GET['CommentId']);

    if ($user->getId() !== $comment->getUser()->getId() && !$this->isGranted('ROLE_ADMIN')) {
      return new Response(StatusCode::NO_ADMIN_RIGHTS);
    }

    if (null === $comment) {
      throw $this->createNotFoundException('No comment found for this id '.$_GET['CommentId']);
    }
    $em->remove($comment);
    $em->flush();

    return new Response(Response::HTTP_OK);
  }

  /**
   * @Route("/comment", name="comment", methods={"POST"})
   */
  public function postCommentAction(CatroNotificationService $notification_service, ProgramManager $program_manager): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user) {
      return new Response(StatusCode::NOT_LOGGED_IN);
    }

    $user = $this->get('security.token_storage')->getToken()->getUser();

    $program = $program_manager->find($_POST['ProgramId']);

    $temp_comment = new UserComment();
    $temp_comment->setUsername($user->getUsername());
    $temp_comment->setUser($user);
    $temp_comment->setText($_POST['Message']);
    $temp_comment->setProgram($program);
    $temp_comment->setUploadDate(date_create());
    $temp_comment->setIsReported(false);

    $em = $this->getDoctrine()->getManager();
    $em->persist($temp_comment);
    $em->flush();

    $em->refresh($temp_comment);

    if ($user !== $program->getUser()) {
      $notification = new CommentNotification($program->getUser(), $temp_comment);
      $notification_service->addNotification($notification);

      // Telling the new comment the CommentNotification it triggered. This is necessary to ensure the
      // correct remove-cascade
      $temp_comment->setNotification($notification);
      $em->persist($temp_comment);
      $em->flush();
    }

    return new Response(Response::HTTP_OK);
  }

  /**
   * @Route("/translateComment", name="translate_comment", methods={"GET"})
   */
  public function translateCommentAction(TranslationDelegate $translationDelegate): Response
  {
    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository(UserComment::class)->find($_GET['CommentId']);

    if (null === $comment) {
      return new Response('No comment found for this id '.$_GET['CommentId'], Response::HTTP_BAD_REQUEST);
    }

    $source_language = $_GET['SourceLanguage'] ?? null;
    $target_language = $_GET['TargetLanguage'];

    if ($source_language === $target_language) {
      return new Response('source and target languages are the same', Response::HTTP_BAD_REQUEST);
    }

    try {
      $translation_result = $translationDelegate->translate($comment->getText(), $source_language, $target_language);
    } catch (InvalidArgumentException $exception) {
      return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
    }

    if (null === $translation_result) {
      return new Response('translation unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
    }

    return new JsonResponse([
      'id' => $_GET['CommentId'],
      'source_language' => $source_language ?? $translation_result->detected_source_language,
      'target_language' => $target_language,
      'translation' => $translation_result->translation,
      'provider' => $translation_result->provider,
    ]);
  }
}
