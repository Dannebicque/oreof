/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/parcours_wizard_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:20
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
    step: String,
    dpeParcours: String,
  }

  connect() {
    this._loadStep(this.stepValue)
  }

  changeStep(event) {
    this._loadStep(event.params.step)
  }

  async _loadStep(step) {
    const response = await fetch(`${this.urlValue + this.dpeParcoursValue}/${step}`)
    this.contentTarget.innerHTML = await response.text()
  }
}
