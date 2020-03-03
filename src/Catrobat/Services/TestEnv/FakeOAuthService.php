<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\OAuthService;
use App\Catrobat\Services\TokenGenerator;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class FakeOAuthService.
 */
class FakeOAuthService extends OAuthService
{
  /**
   * @var OAuthService
   */
  private $oauth_service;

  /**
   * @var mixed
   */
  private $use_real_oauth_service;

  /**
   * @var UserManager
   */
  private $user_manager;

  /**
   * FakeOAuthService constructor.
   */
  public function __construct(OAuthService $oauth_service, ParameterBagInterface $parameter_bag,
                              UserManager $user_manager, ValidatorInterface $validator, ProgramManager $program_manager,
                              EntityManagerInterface $em, TranslatorInterface $translator,
                              TokenStorageInterface $token_storage, EventDispatcherInterface $dispatcher,
                              RouterInterface $router, TokenGenerator $token_generator)
  {
    parent::__construct($user_manager, $parameter_bag, $validator, $program_manager, $em, $translator,
                              $token_storage, $dispatcher, $router, $token_generator);

    $this->oauth_service = $oauth_service;
    try
    {
      $this->use_real_oauth_service = $parameter_bag->get('oauth_use_real_service');
    }
    catch (\Exception $e)
    {
      $this->use_real_oauth_service = false;
    }

    $this->user_manager = $user_manager;
  }

  /**
   * @throws \Exception
   *
   * @return JsonResponse
   */
  public function isOAuthUser(Request $request)
  {
    return $this->oauth_service->isOAuthUser($request);
  }

  /**
   * @throws \Exception
   *
   * @return JsonResponse
   */
  public function checkEMailAvailable(Request $request)
  {
    return $this->oauth_service->checkEMailAvailable($request);
  }

  /**
   * @throws \Exception
   *
   * @return JsonResponse
   */
  public function checkUserNameAvailable(Request $request)
  {
    return $this->oauth_service->checkUserNameAvailable($request);
  }

  /**
   * @throws \Exception
   *
   * @return JsonResponse
   */
  public function checkGoogleServerTokenAvailable(Request $request)
  {
    return $this->oauth_service->checkGoogleServerTokenAvailable($request);
  }

  /**
   * @throws \Exception
   *
   * @return OAuthService|JsonResponse
   */
  public function exchangeGoogleCodeAction(Request $request)
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->exchangeGoogleCodeAction($request);
    }
    /**
     * @var User
     * @var Request $request
     */
    $retArray = [];
    $user = $this->user_manager->findUserByEmail($request->get('email'));
    if (null != $user)
    {
      $retArray['statusCode'] = 200;
      $retArray['answer'] = 'Login successful!';
    }
    else
    {
      $user = $this->user_manager->createUser();
      $user->setUsername('PocketGoogler');
      $user->setEmail('pocketcodetester@gmail.com');
      $user->setPlainPassword('password');
      $retArray['statusCode'] = 201;
      $retArray['answer'] = 'Registration successful!';
    }
    $user->setGplusUid('105155320106786463089');
    $user->setCountry('de');
    $user->setGplusAccessToken('just invalid fake');
    $user->setGplusIdToken('another fake');
    $user->setGplusRefreshToken('the worst fake');
    $user->setEnabled(true);
    $this->user_manager->updateUser($user);

    return JsonResponse::create($retArray);
  }

  /**
   * @throws \Exception
   *
   * @return OAuthService|JsonResponse
   */
  public function loginWithGoogleAction(Request $request)
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->loginWithGoogleAction($request);
    }
    $retArray = [];
    $retArray['token'] = '123';
    $retArray['username'] = 'PocketGoogler';

    return JsonResponse::create($retArray);
  }

  /**
   * @throws \Exception
   *
   * @return JsonResponse
   */
  public function getGoogleUserProfileInfo(Request $request)
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->isOAuthUser($request);
    }
    throw new \Exception('Function not implemented in FakeOAuthService');
  }

  /**
   * @throws \Exception
   *
   * @return JsonResponse
   */
  public function loginWithTokenAndRedirectAction(Request $request)
  {
    return $this->oauth_service->loginWithTokenAndRedirectAction($request);
  }

  /**
   * @throws \Exception
   *
   * @return JsonResponse
   */
  public function deleteOAuthTestUserAccounts()
  {
    if ($this->use_real_oauth_service)
    {
      return $this->oauth_service->deleteOAuthTestUserAccounts();
    }
    throw new \Exception('Function not implemented in FakeOAuthService');
  }

  /**
   * @param $use_real
   */
  public function useRealService($use_real)
  {
    $this->use_real_oauth_service = $use_real;
  }

  /**
   * @return mixed
   */
  public function getUseRealOauthService()
  {
    return $this->use_real_oauth_service;
  }
}
