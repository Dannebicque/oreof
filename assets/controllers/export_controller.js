/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/mutualise_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 09:25
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    url: String,
  }

  static targets = ['liste']

  connect() {
    console.log('connect')
  }

  async changeListe() {
    const annee = document.getElementById('annee_universitaire').value
    const composante = document.getElementById('composante').value

    if (annee !== '' || composante !== '') {
      const body = new URLSearchParams({
        annee,
        composante,
      })

      const response = await fetch(`${this.urlValue}?${body.toString()}`)
      this.listeTarget.innerHTML = await response.text()
    }
  }
}
