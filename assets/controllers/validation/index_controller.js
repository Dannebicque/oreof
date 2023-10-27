/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/centre_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/02/2023 08:50
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    urlListe: String,
  }

  static targets = ['liste']

  connect() {
    this._updateListe()
  }

  async _updateListe() {
    this.listeTarget.innerHTML = window.da.loaderStimulus
    fetch(this.urlListeValue)
      .then((response) => response.text())
      .then((html) => {
        this.listeTarget.innerHTML = html
      })
  }
}
