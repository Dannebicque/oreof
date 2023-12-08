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
    const checkboxes = document.querySelectorAll(this.classCheckAllValue)
    checkboxes.forEach((checkbox) => {
      if (checkbox.disabled === false) {
        checkbox.checked = event.target.checked
      }
    })
  }

  check(event) {
    const checkAll = document.getElementById(this.idCheckAllValue)
    if (event.target.checked) {
      const checkboxes = document.querySelectorAll(this.classCheckAllValue)
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
