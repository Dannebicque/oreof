/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/composante/gestion_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 24/11/2023 15:32
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../../js/callOut'

export default class extends Controller {
  valideConseil() {
    const liste = document.querySelectorAll('.check-all:checked')
    if (liste.length === 0) {
      callOut('Veuillez sélectionner au moins une formation', 'danger')
    } else {
      const formations = []
      liste.forEach((item) => {
        formations.push(item.value)
      })
    }
  }
}
