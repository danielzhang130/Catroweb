<?php

namespace Translation;

use App\Translation\TranslationApiInterface;
use App\Translation\TranslationDelegate;
use App\Translation\TranslationResult;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @internal
 * @covers \App\Translation\TranslationDelegate
 */
class TranslationDelegateTest extends TestCase
{
  public function testSingleApi()
  {
    $api = $this->createMock(TranslationApiInterface::class);
    $expected_result = new TranslationResult();
    $api->expects($this->once())
      ->method('translate')
      ->willReturn($expected_result)
    ;

    $translation_delegate = new TranslationDelegate($api);

    $actual_result = $translation_delegate->translate('test', 'en', 'fr');

    $this->assertEquals($expected_result, $actual_result);
  }

  public function testTryNextApiIfFirstFails()
  {
    $api1 = $this->createMock(TranslationApiInterface::class);
    $api2 = $this->createMock(TranslationApiInterface::class);

    $api1->expects($this->once())
      ->method('translate')
      ->willReturn(null)
    ;

    $expected_result = new TranslationResult();
    $api2->expects($this->once())
      ->method('translate')
      ->willReturn($expected_result)
    ;

    $translation_delegate = new TranslationDelegate($api1, $api2);

    $actual_result = $translation_delegate->translate('test', 'en', 'fr');

    $this->assertEquals($expected_result, $actual_result);
  }

  public function testAllApiFails()
  {
    $api1 = $this->createMock(TranslationApiInterface::class);
    $api2 = $this->createMock(TranslationApiInterface::class);

    $api1->expects($this->once())
      ->method('translate')
      ->willReturn(null)
    ;

    $api2->expects($this->once())
      ->method('translate')
      ->willReturn(null)
    ;

    $translation_delegate = new TranslationDelegate($api1, $api2);

    $result = $translation_delegate->translate('test', 'en', 'fr');

    $this->assertNull($result);
  }

  public function testInvalidLanguageCode()
  {
    $translation_delegate = new TranslationDelegate();
    $invalid_code = [
      '',
      'x',
      'xx',
      'EN', // need to be lowercase en
      'xxxxx',
      'en-XX',
      'en-us', // need to be uppercase US
      'xx-US',
    ];

    foreach ($invalid_code as $code) {
      try {
        $translation_delegate->translate('test', $code, $code);
        $this->fail('Should have thrown exception for language code '.$code);
      } catch (Throwable $ex) {
        $this->assertInstanceOf(InvalidArgumentException::class, $ex);
      }
    }
  }
}
