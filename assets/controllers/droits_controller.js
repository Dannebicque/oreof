/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/droits_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/02/2023 17:23
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../js/callOut'

export default class extends Controller {
  static values = {
    url: String,
  }

  sauvegarde(event) {
    const data = {
      method: 'POST',
      body: JSON.stringify({
        code: event.target.value,
        checked: event.target.checked,
        role: event.params.role,
      }),
    }

    fetch(this.urlValue, data).then(() => {
      callOut('Droits sauvegardés', 'success')
    })
  }
}
