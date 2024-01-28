/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/check_all_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/08/2023 08:38
 */
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    idCheckAll: {
      type: String,
      default: 'check-all',
    },
    classCheckAll: {
      type: String,
      default: '.check-all',
    },
  }

  checkAll(event) {
    // soit event.params.classCheck si existe et non null sinon this.classCheckAllValue
    const classCheckAll = event.params.classcheckall ? `.${event.params.classcheckall}` : this.classCheckAllValue
    const checkboxes = document.querySelectorAll(classCheckAll)
    checkboxes.forEach((checkbox) => {
      if (checkbox.disabled === false) {
        checkbox.checked = event.target.checked
      }
    })
  }

  check(event) {
    const classCheckAll = event.params.classcheckall ? `.${event.params.classcheckall}` : this.classCheckAllValue
    const idCheckAll = event.params.idcheckall ? event.params.idcheckall : this.idCheckAllValue
    console.log(classCheckAll, idCheckAll)
    const checkAll = document.getElementById(idCheckAll)
    if (event.target.checked) {
      const checkboxes = document.querySelectorAll(classCheckAll)
      let checked = true
      checkboxes.forEach((checkbox) => {
        if (checkbox.disabled === false) {
          if (!checkbox.checked) {
            checked = false
          }
        }
      })
      checkAll.checked = checked
    } else {
      checkAll.checked = false
    }
  }
}
