/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec_wizard_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 18/02/2023 21:43
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = [
    'content',
    'synthese',
  ]

  currentStep = 1

  currentType = ''

  static values = {
    url: String,
    urlSynthese: String,
    ficheMatiere: String,
  }

  connect() {
    this.currentStep = 1
    this.currentType = ''
    this._loadSynthese()
    this._loadStep(this.currentStep, this.currentType)
  }

  refreshStep() {
    this._loadSynthese()
    this._loadStep(this.currentStep, this.currentType)
  }

  async _loadSynthese() {
    const response = await fetch(this.urlSyntheseValue)
    this.syntheseTarget.innerHTML = await response.text()
  }

  changeStep(event) {
    this._loadStep(event.params.step, event.params.type ?? '')
  }

  async _loadStep(step, type = '') {
    this.currentStep = step
    this.currentType = type
    if (type !== '') {
      const response = await fetch(`${this.urlValue + this.ficheMatiereValue}/${step}/${type}`)
      this.contentTarget.innerHTML = await response.text()
    } else {
      const response = await fetch(`${this.urlValue + this.ficheMatiereValue}/${step}`)
      this.contentTarget.innerHTML = await response.text()
    }
  }
}
