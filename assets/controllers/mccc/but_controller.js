/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/mccc/but_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 02/09/2023 17:28
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  changePourcentage() {
    // récupérer chaque input avec la class pourcentage, faire la somme. Si > 100, alerter. Si < 100, alerter.
    this._verifPourcentage()
  }

  changeNbNotes() {
    // récupérer chaque input avec la class pourcentage, faire la somme. Si > 100, alerter. Si < 100, alerter.
    this._verifPourcentage()
  }

  _verifPourcentage() {
    let total = 0.0
    this.element.querySelectorAll('.pourcentage').forEach((item) => {
      if (item.value !== '') {
        // récupérer le champ "nbnotes" le plus proche juste après
        const nbNotes = item.closest('.row').querySelector('.nbnotes').value
        total += parseFloat(item.value) * parseInt(nbNotes, 10)
      }
    })

    let message = ''
    if (total > 100.0) {
      message = '<div class="alert alert-danger">La somme des pourcentages ne peut pas dépasser 100</div>'
    }

    if (total < 100.0) {
      message = '<div class="alert alert-danger">La somme des pourcentages doit être égale à 100</div>'
    }

    document.getElementById('erreur').innerHTML = message // todo: mettre un alert... Live component en changeant le data ?
  }
}
