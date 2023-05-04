/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/formation_wizard_controller.js
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
    urlParcours: String,
    formation: String,
    step: String,
  }

  connect() {
    this._loadStep(this.stepValue)
  }

  changeStep(event) {
    this._loadStep(event.params.step)
  }

  refreshStep() {
    this._loadStep(1)
  }

  changeStepParcours(event) {
    this._loadStepParcours(event.params.step, event.params.parcours)
  }

  async _loadStep(step) {
    const response = await fetch(`${this.urlValue + this.formationValue}/${step}`)
    this.contentTarget.innerHTML = await response.text()
  }

  async _loadStepParcours(step, parcours) {
    const response = await fetch(`${this.urlParcoursValue + parcours}/${step}`)
    this.contentTarget.innerHTML = await response.text()
  }
}
