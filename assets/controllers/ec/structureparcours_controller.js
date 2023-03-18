/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/structureparcours_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 07/03/2023 20:38
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  changeModalite(event) {
    const valeur = event.target.value

    const blocPresentiel = document.getElementById('bloc_presentiel')
    const blocDistanciel = document.getElementById('bloc_distanciel')

    if (valeur === '0') {
      // présentiel
      blocPresentiel.style.display = 'block'
      blocDistanciel.style.display = 'none'
    }

    if (valeur === '1') {
      // hybride
      blocPresentiel.style.display = 'block'
      blocDistanciel.style.display = 'block'
    }

    if (valeur === '2') {
      // distanciel
      blocPresentiel.style.display = 'none'
      blocDistanciel.style.display = 'block'
    }
  }
}
