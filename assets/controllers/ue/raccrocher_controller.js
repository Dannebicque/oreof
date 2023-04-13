/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/mutualise_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 09:25
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../../js/callOut'

export default class extends Controller {
  static values = {
    url: String,
  }

  valideReutilise(event) {
    event.preventDefault()
    const { value } = document.getElementById('raccrocher')
    if (value !== '') {
      if (confirm('Voulez-vous vraiment réutiliser cette UE ?')) {
        const body = {
          method: 'POST',
          body: JSON.stringify({
            field: 'raccrocher',
            value,
          }),
        }
        fetch(this.urlValue, body).then(() => {
          callOut('Réutilisation effectuée', 'success')
          // dispatch modal close
          this.dispatch('modalHide')
        })
      }
    } else {
      callOut('Vous devez choisir un parcours dans la liste', 'warning')
      document.getElementById('raccrocher').classList.add('is-invalid')
    }
  }
}
