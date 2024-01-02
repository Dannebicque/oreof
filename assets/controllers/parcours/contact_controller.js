/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/parcours/bcc_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/04/2023 16:41
 */
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    url: String,
  }

  static targets = ['liste']

  connect() {
    this._load()
  }

  async _load() {
    this.listeTarget.innerHTML = window.da.loaderStimulus
    const response = await fetch(this.urlValue)
    this.listeTarget.innerHTML = await response.text()
  }
}
