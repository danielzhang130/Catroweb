/* eslint-env jquery */

// eslint-disable-next-line no-unused-vars
function Translation (hasDescription, hasCredit) {

  $(document).on('click', '.comment-translation-button', function () {
    const commentId = $(this).attr('id').substring('comment-translation-button-'.length)

    const textElement = $('#comment-text-' + commentId)
    const translationTextElement = $('#comment-text-translation-' + commentId)
    const translationCreditElement = $('#comment-translation-credit-' + commentId)
    translateContent(textElement, translationTextElement, translationCreditElement)

    $(this).hide()
    $('#remove-comment-translation-button-' + commentId).show()
    $('#comment-text-wrapper-' + commentId).slideUp()
    $('#comment-translation-wrapper-' + commentId).slideDown()
  })

  $(document).on('click', '.remove-comment-translation-button', function () {
    const commentId = $(this).attr('id').substring('remove-comment-translation-button-'.length)
    $(this).hide()
    $('#comment-translation-button-' + commentId).show()
    $('#comment-translation-wrapper-' + commentId).slideUp()
    $('#comment-text-wrapper-' + commentId).slideDown()
  })

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

  function translateContent(textElement, translationTextElement, translationCreditElement) {
    // var list = '{{ countries|json_encode|raw }}';
    content = textElement.text()
    translationCreditElement.text("Translated by iTranslate from English")
    translationTextElement.text(content + " translation")
    // $.ajax({
    //   url: '../api/comment/1/translate',
    //   type: 'get',
    //   data: { text: "test", target_language="fr" },
    //   success: function (data) {
    //     console.log("test success")
    //   },
    //   error: function () {
    //     console.log("test error")
    //   }
    // })
  }

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
