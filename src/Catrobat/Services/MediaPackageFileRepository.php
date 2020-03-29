<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use Imagick;
use ImagickException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class MediaPackageFileRepository
{
  private string $dir;
  private string $path;
  private Filesystem $filesystem;
  private string $thumb_dir;

  public function __construct(ParameterBagInterface $parameter_bag)
  {
    /** @var string $dir Directory where media package files are stored */
    $dir = $parameter_bag->get('catrobat.mediapackage.dir');
    /** @var string $path Path where files in $dir can be accessed via web */
    $path = $parameter_bag->get('catrobat.mediapackage.path');
    $dir = preg_replace('#([^/]+)$#', '$1/', $dir);
    $path = preg_replace('#([^/]+)$#', '$1/', $path);
    $thumb_dir = $dir.'thumbs/';

    if (!is_dir($dir))
    {
      throw new InvalidStorageDirectoryException($dir.' is not a valid directory');
    }

    if (!is_dir($thumb_dir) && !mkdir($thumb_dir))
    {
      throw new InvalidStorageDirectoryException($thumb_dir.' is not a valid directory');
    }

    $this->dir = $dir;
    $this->path = $path;
    $this->filesystem = new Filesystem();
    $this->thumb_dir = $thumb_dir;
  }

  /**
   * Saves a file, uploaded by the user, to the media package directory
   * and creates a thumbnail, if chosen.
   *
   * @param File   $file             the uploaded file handle
   * @param int    $id               the database id of the file
   * @param string $extension        File extension
   * @param bool   $create_thumbnail Whether a thumbnail should be created or not. Default is true.
   *
   * @throws ImagickException
   */
  public function save(File $file, int $id, string $extension, bool $create_thumbnail = true): void
  {
    $file->move($this->dir, $this->generateFileNameFromId((string) $id, $extension));
    if ($create_thumbnail)
    {
      $this->createThumbnail((string) $id, $extension);
    }
  }

  /**
   * Copies a file to the media package directory.
   * Used in test cases.
   *
   * @param File   $file             the source file to copy
   * @param int    $id               the database id of the file
   * @param string $extension        file extension
   * @param bool   $create_thumbnail Whether a thumbnail should be created or not. Default is true.
   *
   * @throws ImagickException
   */
  public function saveMediaPackageFile(File $file, int $id, string $extension,
                                       bool $create_thumbnail = true): void
  {
    $target = $this->dir.$this->generateFileNameFromId((string) $id, $extension);
    $this->filesystem->copy($file->getPathname(), $target);
    if ($create_thumbnail)
    {
      $this->createThumbnail((string) $id, $extension);
    }
  }

  /**
   * Removes a file and its thumbnail from the disk.
   *
   * @param int    $id        the database id of the file
   * @param string $extension File extension
   */
  public function remove(int $id, string $extension): void
  {
    $file_name = $this->generateFileNameFromId((string) $id, $extension);
    $path = $this->dir.$file_name;
    if (is_file($path))
    {
      unlink($path);
    }

    $thumb = $this->thumb_dir.$file_name;
    if (is_file($thumb))
    {
      unlink($thumb);
    }
  }

  /**
   * Creates missing thumbnails.
   * It checks for files that exist in the base directory but not in the thumbs directory.
   *
   * @throws ImagickException
   */
  public function createMissingThumbnails(): void
  {
    $finder = new Finder();
    $finder->files()->in($this->dir)->depth(0);

    /** @var \SplFileInfo $file */
    foreach ($finder as $file)
    {
      $ext = $file->getExtension();
      $basename = $file->getBasename('.'.$ext);

      if (!is_file($this->thumb_dir.$basename.'.jpeg'))
      {
        $ignored_extensions = ['adp', 'au', 'mid', 'mp4a', 'mpga', 'oga', 's3m', 'sil', 'uva',
          'eol', 'dra', 'dts', 'dtshd', 'lvp', 'pya', 'ecelp4800', 'ecelp7470', 'ecelp9600', 'rip',
          'weba', 'aac', 'aif', 'caf', 'flac', 'mka', 'm3u', 'wax', 'wma', 'ram', 'rmp', 'wav',
          'xm', '3gp', '3g2', 'h261', 'h263', 'h264', 'jpgv', 'jpm', 'mj2', 'mp4', 'mpeg', 'ogv',
          'qt', 'uvh', 'uvm', 'uvp', 'uvs', 'uvv', 'dvb', 'fvt', 'mxu', 'pyv', 'uvu', 'viv',
          'webm', 'f4v', 'fli', 'flv', 'm4v', 'mkv', 'mng', 'asf', 'vob', 'wm', 'wmv', 'wmx',
          'wvx', 'avi', 'movie', 'smv', 'pdf', 'txt', 'rtx', 'zip', '7z', ];
        if (!in_array($file->getExtension(), $ignored_extensions, true))
        {
          echo 'Create Thumbnail for '.$file->getFilename().PHP_EOL;
          $this->createThumbnail($basename, $ext);
        }
      }
    }
  }

  /**
   * Returns the web path of a given id and extension.
   *
   * @param int    $id        the database id of the file
   * @param string $extension File extension
   */
  public function getWebPath(int $id, string $extension): string
  {
    return $this->path.$this->generateFileNameFromId((string) $id, $extension);
  }

  /**
   * Returns a file handle of the media file.
   *
   * @param int    $id        the database id of the file
   * @param string $extension File extension
   */
  public function getMediaFile(int $id, string $extension): File
  {
    return new File($this->dir.$id.'.'.$extension);
  }

  /**
   * Creates a thumbnail for the given id and extension.
   *
   * @param string $id        the id/name of the file
   * @param string $extension File extension
   *
   * @throws ImagickException
   */
  private function createThumbnail(string $id, string $extension): void
  {
    try
    {
      $path = $this->dir.$this->generateFileNameFromId($id, $extension);
      $imagick = new Imagick(realpath($path));
      $meanImg = clone $imagick;
      $meanImg->setBackgroundColor('#ffffff');
      $meanImg->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
      $meanImg->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
      $meanImg->setImageFormat('jpeg');
      $meanImg->setColorspace(Imagick::COLORSPACE_GRAY);
      $mean = $meanImg->getImageChannelMean(Imagick::CHANNEL_GRAY);

      $background = '#ffffff';
      if ($mean['mean'] > 0xD000 && $mean['standardDeviation'] < 2_000)
      {
        $background = '#888888';
      }

      $imagick->setImageBackgroundColor($background);
      $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
      $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
      $imagick->setImageFormat('jpeg');
      $imagick->thumbnailImage(200, 0);
      $imagick->writeImage($this->thumb_dir.$id.'.'.'jpeg');
    }
    catch (ImagickException $imagickException)
    {
      $code = $imagickException->getCode() % 100;
      // for error codes see: https://www.imagemagick.org/script/exception.php
      // allowed: 20 non-images/unknown type; 5 font unavailable (svg etc.)
      if (20 !== $code && 5 !== $code)
      {
        throw $imagickException;
      }
    }
  }

  /**
   * Generates a file name from given id and extension.
   *
   * @param string $id        the id/name of the file
   * @param string $extension File extension
   */
  private function generateFileNameFromId(string $id, string $extension): string
  {
    return $id.'.'.$extension;
  }
}
