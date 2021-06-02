/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function Translation (hasDescription, hasCredit) {
  let languageMap = {}
  let targetLanguage = document.documentElement.lang

  $(document).ready(function () {
    $.ajax({
      url: '../languages',
      type: 'get',
      success: function (data) {
        languageMap = data
      }
    })
  })

  $('.mdc-list-item__text').each(function () {
    if ($(this).parent()[0].hasAttribute('selected')) {
      targetLanguage = fixLanguageCode($(this).parent().attr('data-value'))
    }
  })

  $(document).on('click', '.comment-translation-button', function () {
    const commentId = $(this).attr('id').substring('comment-translation-button-'.length)

    $(this).hide()

    if ($('#comment-text-translation-' + commentId).attr('lang') !== targetLanguage) {
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

  function fixLanguageCode (languageCode) {
    if (languageCode.length > 2) {
      languageCode = languageCode.split('')
      languageCode[2] = '-'
      languageCode = languageCode.join('')
    }

    return languageCode
  }

  function getTranslationProvider (provider) {
    if (provider === 'itranslate') {
      return 'iTranslate'
    }
    return ''
  }

  function openGoogleTranslatePage (commentId) {
    const text = document.getElementById('comment-text-' + commentId).innerText
    window.open(
      'https://translate.google.com/?q=' + encodeURIComponent(text) + '&sl=auto&tl=' + targetLanguage,
      '_self'
    )
  }

  function setTranslatedCommentData (commentId, data) {
    $('#comment-text-translation-' + commentId).text(data.translation)
    $('#comment-text-translation-' + commentId).attr('lang', data.target_language)

    const provider = $('#comment-translation-provider-' + commentId)
      .text()
      .replace('%provider%', getTranslationProvider(data.provider))
    $('#comment-translation-provider-' + commentId).text(provider)
    $('#comment-translation-source-language-' + commentId).text(languageMap[data.source_language])
    $('#comment-translation-target-language-' + commentId).text(languageMap[data.target_language])
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

  translation(
    document.getElementById('translate-program'),
    elementsToTranslate,
    document.documentElement.lang
  )

  function translation (buttonElement, textElements, targetLang) {
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
