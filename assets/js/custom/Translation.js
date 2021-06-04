/* eslint-env jquery */
/* global Routing */

// eslint-disable-next-line no-unused-vars
function Translation (hasDescription, hasCredit) {
  const providerMap = {
    itranslate: 'iTranslate'
  }
  let languageMap = {}
  let targetLanguage = document.documentElement.lang
  let creditText = 'Translated by %provider% from %sourceLanguage% to %targetLanguage%'

  $(document).ready(function () {
    setTargetLanguage()
    getLanguageMap()
    getTranslationCreditText()
  })

  $(document).on('click', '.comment-translation-button', function () {
    const commentId = $(this).attr('id').substring('comment-translation-button-'.length)

    $(this).hide()

    if (isTranslationAvailable(commentId)) {
      $('#comment-translation-loading-spinner-' + commentId).show()
      translateComment(commentId)
    } else {
      openTranslatedComment(commentId)
    }
  })

  $(document).on('click', '.remove-comment-translation-button', function () {
    const commentId = $(this).attr('id').substring('remove-comment-translation-button-'.length)
    $(this).hide()
    $('#comment-translation-button-' + commentId).show()
    $('#comment-translation-wrapper-' + commentId).slideUp()
    $('#comment-text-wrapper-' + commentId).slideDown()
  })

  function setTargetLanguage () {
    const decodedCookie = decodeURIComponent(document.cookie).split(';')
    for (let i = 0; i < decodedCookie.length; i++) {
      if (decodedCookie[i].includes('hl=') && decodedCookie[i].length < 10) {
        targetLanguage = decodedCookie[i].substring(decodedCookie[i].indexOf('=') + 1).replace('_', '-')
      }
    }
    console.log(targetLanguage)
  }

  function getLanguageMap () {
    $.ajax({
      url: '../languages',
      type: 'get',
      success: function (data) {
        languageMap = data
      }
    })
  }

  function getTranslationCreditText () {
    const url = Routing.generate('translate', {
      word: 'programs.provider',
      domain: 'catroweb'
    })
    $.get(url, function (data) {
      creditText = data
    })
  }

  function isTranslationAvailable (commentId) {
    return $('#comment-text-translation-' + commentId).attr('lang') !== targetLanguage
  }

  function openGoogleTranslatePage (commentId) {
    const text = document.getElementById('comment-text-' + commentId).innerText
    window.open(
      'https://translate.google.com/?q=' + encodeURIComponent(text) + '&sl=auto&tl=' + targetLanguage,
      '_self'
    )
  }

  function setSourceAndTargetLanguage (commentId, firstLang, secondLang, firstLangMapped, secondLangMapped) {
    const transition = creditText.substring(creditText.indexOf(firstLang) + firstLang.length, creditText.indexOf(secondLang))
    
    $('#comment-translation-credit-transition-' + commentId).text(transition)
    $('#comment-translation-first-language-' + commentId).text(firstLangMapped)
    $('#comment-translation-second-language-' + commentId).text(secondLangMapped)
  }

  function setTranslatedCommentData (commentId, data) {
    $('#comment-text-translation-' + commentId).text(data.translation)
    $('#comment-text-translation-' + commentId).attr('lang', data.target_language)

    let provider = creditText.replace('%provider%', providerMap[data.provider])
    provider = provider.substring(0, provider.indexOf('%'))
    $('#comment-translation-provider-' + commentId).text(provider)

    if (creditText.indexOf('%sourceLangauge%') < creditText.indexOf('%targetLanguage%')) {
      setSourceAndTargetLanguage(commentId, '%sourceLangauge%', '%targetLanguage%', languageMap[data.source_language], languageMap[data.target_language])
    } else {
      setSourceAndTargetLanguage(commentId, '%targetLanguage%', '%sourceLangauge%', languageMap[data.target_language], languageMap[data.source_language])
    }
  }

  function openTranslatedComment (commentId) {
    $('#comment-translation-loading-spinner-' + commentId).hide()
    $('#remove-comment-translation-button-' + commentId).show()
    $('#comment-translation-wrapper-' + commentId).slideDown()
    $('#comment-text-wrapper-' + commentId).slideUp()
  }

  function commentNotTranslated (commentId) {
    $('#comment-translation-loading-spinner-' + commentId).hide()
    $('#comment-translation-button-' + commentId).show()
    openGoogleTranslatePage(commentId)
  }

  function translateComment (commentId) {
    $.ajax({
      url: '../translate/comment/' + commentId,
      type: 'get',
      data: { target_language: targetLanguage },
      success: function (data) {
        setTranslatedCommentData(commentId, data)
        openTranslatedComment(commentId)
      },
      error: function () {
        commentNotTranslated(commentId)
      }
    })
  }

  const elementsToTranslate = [document.getElementById('name')]

  if (hasDescription) {
    elementsToTranslate.push(document.getElementById('description'))
  }
  if (hasCredit) {
    elementsToTranslate.push(document.getElementById('credits'))
  }

  translateWithLink(
    document.getElementById('translate-program'),
    elementsToTranslate,
    document.documentElement.lang
  )

  function translateWithLink (buttonElement, textElements, targetLang) {
    let text = ''
    if (Array.isArray(textElements)) {
      const array = []
      for (let i = 0; i < textElements.length; i++) {
        array.push(textElements[i].innerText)
      }

      text = array.join('\n\n')
    } else {
      text = textElements.innerText
    }

    buttonElement.setAttribute('href', 'https://translate.google.com/?q=' + encodeURIComponent(text) + '&sl=auto&tl=' + targetLang)
  }
}
