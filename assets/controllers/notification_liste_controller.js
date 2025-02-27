/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/notification_liste_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/03/2023 12:13
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../js/callOut'

export default class extends Controller {
  static values = {
    url: String,
  }

  async change(event) {
    const body = {
      method: 'POST',
      body: JSON.stringify({
        value: event.target.checked,
        idNotif: event.params.id,
        toNotif: event.params.to,
      }),
    }

    await fetch(this.urlValue, body).then((response) => response.json()).then((data) => {
      if (data === true) {
        callOut('Sauvegarde effectuée', 'success')
      } else {
        callOut('Erreur lors de la sauvegarde', 'danger')
      }
    })
  }
}
