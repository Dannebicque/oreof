/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/check_all_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/08/2023 08:38
 */
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  checkAll(event) {
    const checkboxes = document.querySelectorAll('.check-all')
    checkboxes.forEach((checkbox) => {
      checkbox.checked = event.target.checked
    })
  }

  check(event) {
    const checkAll = document.getElementById('check-all')
    if (event.target.checked) {
      const checkboxes = document.querySelectorAll('.check-all')
      let checked = true
      checkboxes.forEach((checkbox) => {
        if (!checkbox.checked) {
          checked = false
        }
      })
      checkAll.checked = checked
    } else {
      checkAll.checked = false
    }
  }
}
