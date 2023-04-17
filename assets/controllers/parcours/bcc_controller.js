/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/parcours/bcc_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/04/2023 16:41
 */
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  changeBcc(event) {
    if (event.target.checked) {
      document.getElementById(`bcc_${event.params.id}_competence`).classList.remove('d-none')
    } else {
      document.getElementById(`bcc_${event.params.id}_competence`).classList.add('d-none')
      document.querySelectorAll(`.bcc_${event.params.id}`).forEach((element) => {
        element.checked = false
      })
      this._save({ action: 'removeBcc', value: event.params.id })
    }
  }
}
