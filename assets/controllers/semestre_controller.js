/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/semestre_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 04/04/2023 20:24
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['zone']

  static values = {
    url: String,
  }

  async changeTypeSemestre(event) {
    if (event.target.value === 'standard') {
      this.zoneTarget.innerHTML = ''
    } else {
      const body = {
        method: 'POST',
        body: JSON.stringify({
          action: event.target.value,
        }),
      }
      const response = await fetch(`${this.urlValue}`, body)
      this.zoneTarget.innerHTML = await response.text()
    }
  }
}
