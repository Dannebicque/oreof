/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/centre_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 16/02/2023 08:50
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../../js/callOut'

export default class extends Controller {
  static targets = ['action']

  connect() {
    console.log('fiche matiere controller')
  }

  detail(event) {
    if (event.target.dataset.state === 'open') {
      document.getElementById(`detail_parcours_${event.params.parcours}`).classList.add('d-none')
      event.target.dataset.state = 'close'
      event.target.firstElementChild.classList.add('fa-caret-right')
      event.target.firstElementChild.classList.remove('fa-caret-down')
    } else {
      document.getElementById(`detail_parcours_${event.params.parcours}`).classList.remove('d-none')
      event.target.dataset.state = 'open'
      event.target.firstElementChild.classList.remove('fa-caret-right')
      event.target.firstElementChild.classList.add('fa-caret-down')
    }
  }

  async valide(event) {
    const liste = document.querySelectorAll('.check-all:checked')
    if (liste.length === 0) {
      callOut('Veuillez sélectionner au moins une fiche EC/matière', 'danger')
    } else {
      const fiches = []
      liste.forEach((item) => {
        fiches.push(item.value)
      })

      await fetch(`${event.params.url}`, {
        method: 'POST',
        body: JSON.stringify({
          fiches: fiches.toString(),
        }),
      }).then((response) => {
        if (response.status === 200) {
          callOut('Fiches validées', 'success')
          window.location.reload()
        } else {
          callOut('Une erreur est survenue', 'danger')
        }
      })
    }
  }
}
