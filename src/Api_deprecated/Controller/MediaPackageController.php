<?php

namespace App\Api_deprecated\Controller;

use App\Catrobat\StatusCode;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class MediaPackageController extends AbstractController
{
  /**
   * @deprecated
   *
   * @Route("/api/media/package/{package}/json", name="api_media_lib_package",
   * requirements={"package": "\w+"}, defaults={"_format": "json"}, methods={"GET"})
   */
  public function getMediaFilesForPackage(string $package): JsonResponse
  {
    $em = $this->getDoctrine()->getManager();

    /** @var MediaPackage|null $media_package */
    $media_package = $em->getRepository(MediaPackage::class)
      ->findOneBy(['name' => $package])
    ;
    if (null === $media_package) {
      return JsonResponse::create(
        ['statusCode' => StatusCode::MEDIA_LIB_PACKAGE_NOT_FOUND,
          'message' => $package.' not found', ]
      );
    }
    $json_response_array = [];
    $media_package_categories = $media_package->getCategories();
    if ($media_package_categories->isEmpty()) {
      return JsonResponse::create(
        $json_response_array
      );
    }
    /** @var MediaPackageCategory $media_package_category */
    foreach ($media_package_categories as $media_package_category) {
      $media_package_files = $media_package_category->getFiles();
      if (!$media_package_files->isEmpty()) {
        /** @var MediaPackageFile $media_package_file */
        foreach ($media_package_files as $media_package_file) {
          $json_response_array[] = $this->createArrayOfMediaData($media_package_file);
        }
      }
    }

    return JsonResponse::create(
      $json_response_array
    );
  }

  /**
   * @deprecated
   *
   * @Route("/api/media/packageByNameUrl/{package}/json", name="api_media_lib_package_bynameurl",
   * requirements={"package": "\w+"}, defaults={"_format": "json"}, methods={"GET"})
   */
  public function getMediaFilesForPackageByNameUrl(string $package): JsonResponse
  {
    $em = $this->getDoctrine()->getManager();

    /** @var MediaPackage|null $media_package */
    $media_package = $em->getRepository(MediaPackage::class)
      ->findOneBy(['nameUrl' => $package])
    ;
    if (null === $media_package) {
      return JsonResponse::create(
        ['statusCode' => StatusCode::MEDIA_LIB_PACKAGE_NOT_FOUND,
          'message' => $package.' not found', ]
      );
    }
    $json_response_array = [];
    $media_package_categories = $media_package->getCategories();
    if ($media_package_categories->isEmpty()) {
      return JsonResponse::create(
        $json_response_array
      );
    }
    foreach ($media_package_categories as $media_package_category) {
      /** @var array|MediaPackageFile $media_package_files */
      $media_package_files = $media_package_category->getFiles();
      if (null !== $media_package_files && (is_countable($media_package_files) ? count($media_package_files) : 0) > 0) {
        foreach ($media_package_files as $media_package_file) {
          $json_response_array[] = $this->createArrayOfMediaData($media_package_file);
        }
      }
    }

    return JsonResponse::create(
      $json_response_array
    );
  }

  protected function createArrayOfMediaData(MediaPackageFile $media_package_file): array
  {
    $id = $media_package_file->getId();
    $name = $media_package_file->getName();
    $flavor = $media_package_file->getFlavor();
    $package = $media_package_file->getCategory()->getPackage()->first()->getName();
    $category = $media_package_file->getCategory()->getName();
    $author = $media_package_file->getAuthor();
    $extension = $media_package_file->getExtension();
    $url = $media_package_file->getUrl();
    $download_url = $this->generateUrl('download_media',
      [
        'id' => $id,
      ]);

    return
      [
        'id' => $id,
        'name' => $name,
        'flavor' => $flavor,
        'package' => $package,
        'category' => $category,
        'author' => $author,
        'extension' => $extension,
        'url' => $url,
        'download_url' => $download_url,
      ];
  }
}
