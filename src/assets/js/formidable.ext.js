;(function ($) {
   $(function () {
      if ($('.frm_checkbox').length) {
         $('.frm_checkbox').each(function () {
            let label = $(this).find("label:contains('Terms')")
            console.log(label)
            if (label.length === 0) {
               return
            }
            let labelParent = label.parent()
            let labelHtml = label.html()
            let inputField = label.find('input')

            labelHtml = labelHtml.replace(inputField[0].outerHTML, '')

            const privacyPolicyUrl = window.links.privacyPolicyUrl

            labelHtml = labelHtml.replace(
               'Terms',
               `<a target="_blank" style="text-decoration:underline; color:inherit;" href="${privacyPolicyUrl}">Terms</a>`
            )
            labelParent.prepend(inputField)
            label.html(labelHtml)
         })
      }
   })
})(jQuery)
