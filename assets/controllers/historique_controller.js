/*
 * Copyright (c) 2024. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/historique_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 24/07/2024 08:50
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['entreeHistorique']

  connect() {
    console.log('Connecté')
  }

  delete(event) {
    if (confirm('Voulez-vous vraiment supprimer cette entrée ?')) {
      event.preventDefault()
      const { url } = event.params
      // csrf

      const body = new FormData()
      body.append('_token', event.params.csrf)

      fetch(url, {
        method: 'POST',
        body,
      })
        .then((response) => {
          if (response.status === 200) {
            this.entreeHistoriqueTarget.remove()
          }
        })
    }
  }
}
