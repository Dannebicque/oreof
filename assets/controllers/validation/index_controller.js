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

  async changeListe() {
    this._updateListe()
  }

  async _updateListe() {
    const composante = document.getElementById('composante').value
    const typeValidation = document.getElementById('type_validation').value
    if (composante !== '' && typeValidation !== '') {
      const body = new URLSearchParams({
        composante,
        typeValidation,
      })

      const response = await fetch(`${this.urlListeValue}?${body.toString()}`)
      this.listeTarget.innerHTML = await response.text()
    }
  }
}
