/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/centre_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/02/2023 08:50
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../../js/callOut'
import JsonResponse from '../../js/JsonResponse'

export default class extends Controller {
  static values = {
    urlListe: String,
  }

  static targets = ['liste', 'action']

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

  async valide(event) {
    const liste = document.querySelectorAll('.check-all:checked')
    if (liste.length === 0) {
      this.actionTarget.innerHTML = ''
      callOut('Veuillez sélectionner au moins une formation', 'danger')
    } else {
      const formations = []
      liste.forEach((item) => {
        formations.push(item.value)
      })

      const body = new URLSearchParams({
        formations,
      })

      this.actionTarget.innerHTML = ''
      const reponse = await fetch(`${event.params.url}?${body.toString()}`)
      this.actionTarget.innerHTML = await reponse.text()
    }
  }
}
