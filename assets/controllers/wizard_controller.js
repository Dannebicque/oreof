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
    step: String,
    stepDefault: String,
  }

  connect() {
    // si stepValue est vide récupérer le step dans l'url
    if (this.stepValue === '') {
      const urlParams = new URLSearchParams(window.location.search)

      // si le paramètre step est présent dans l'url on prend, sinon StepDefault
      this.stepValue = urlParams.has('step') ? urlParams.get('step') : this.stepDefaultValue
    }

    this._loadStep(this.stepValue)
  }

  changeStep(event) {
    const url = new URL(window.location);
    const params = new URLSearchParams(url.search);
    params.set('step', event.params.step);
    window.history.pushState({}, '', `${url.pathname}?${params.toString()}`);

    this._loadStep(event.params.step)
  }

  async _loadStep(step) {
    const url = new URL(window.location);
    const params = new URLSearchParams(url.search);
    params.set('step', step);

    const response = await fetch(`${this.urlValue}?${params}`)
    this.contentTarget.innerHTML = await response.text()
  }
}
