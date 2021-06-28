/* eslint-env jquery */
/* global Translation */

// eslint-disable-next-line no-unused-vars
class TranslateProgram extends Translation {
  constructor (translatedByLine, programId, hasDescription, hasCredit) {
    super(translatedByLine)
    this.programId = programId
    this.hasDescription = hasDescription
    this.hasCredit = hasCredit
    this._initListeners()
  }

  _initListeners () {
    const translateProgram = this
    $(document).on('click', '#program-translation-button', function () {
      $(this).hide()

      if (translateProgram.isTranslationNotAvailable('#name-translation')) {
        $('#program-translation-loading-spinner').show()
        translateProgram.translateProgram()
      } else {
        translateProgram.openTranslatedProgram()
      }
    })

    $(document).on('click', '#remove-program-translation-button', function () {
      $(this).hide()
      $('#program-translation-button').show()

      const animationTime = 400
      $('#name').removeClass('program-name').addClass('program-name-animation')
      $('#name-translation').removeClass('program-name').addClass('program-name-animation')
      $('#name-translation').animate({ width: 'toggle' })
      $('#name').animate({ width: 'toggle' }, animationTime,
        function () {
          $('#name').removeClass('program-name-animation').addClass('program-name')
          $('#name-translation').removeClass('program-name-animation').addClass('program-name')
        }
      )

      if (translateProgram.hasDescription) {
        $('#description').slideDown()
        $('#description-translation').slideUp()
      }

      $('#credits-translation-wrapper').slideUp()
      if (translateProgram.hasCredit) {
        $('#credits').slideDown()
      }
    })
  }

  setTranslatedProgramData (data) {
    $('#name-translation').attr('lang', data.target_language)
    $('#name-translation').text(data.translated_title)

    if (this.hasDescription) {
      $('#description-translation').text(data.translated_description)
    }

    if (this.hasCredit) {
      $('#credits-translation').text(data.translated_credit)
    }

    const translationCreditContainers = {
      before: '#program-translation-before-languages',
      between: '#program-translation-between-languages',
      after: '#program-translation-after-languages',
      firstLanguage: '#program-translation-first-language',
      secondLanguage: '#program-translation-second-language'
    }

    this.setTranslationCredit(translationCreditContainers, data)
  }

  openTranslatedProgram () {
    $('#program-translation-loading-spinner').hide()
    $('#remove-program-translation-button').show()

    const animationTime = 400
    $('#name').removeClass('program-name').addClass('program-name-animation')
    $('#name-translation').removeClass('program-name').addClass('program-name-animation')
    $('#name').animate({ width: 'toggle' })
    $('#name-translation').animate({ width: 'toggle' }, animationTime,
      function () {
        $('#name').removeClass('program-name-animation').addClass('program-name')
        $('#name-translation').removeClass('program-name-animation').addClass('program-name')
      }
    )

    if (this.hasDescription) {
      $('#description-translation').slideDown()
      $('#description').slideUp()
    }

    $('#credits-translation-wrapper').slideDown()
    if (this.hasCredit) {
      $('#credits').slideUp()
    }
  }

  programNotTranslated () {
    $('#program-translation-loading-spinner').hide()
    $('#program-translation-button').show()

    let text = document.getElementById('name').innerText

    if (this.hasDescription) {
      text += '\n\n' + document.getElementById('description').innerText
    }

    if (this.hasCredit) {
      text += '\n\n' + document.getElementById('credits').innerText
    }

    this.openGoogleTranslatePage(text)
  }

  translateProgram () {
    const self = this
    $.ajax({
      url: '../translate/project/' + self.programId,
      type: 'get',
      data: { target_language: self.targetLanguage },
      success: function (data) {
        self.setTranslatedProgramData(data)
        self.openTranslatedProgram()
      },
      error: function () {
        self.programNotTranslated()
      }
    })
  }
}
