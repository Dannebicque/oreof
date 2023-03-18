/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/parcours/step5_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/03/2023 21:09
 */

import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { calculEtatStep } from '../../js/calculEtatStep'
import trixEditor from '../../js/trixEditor'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
  }

  connect() {
    document.getElementById('parcours_step5_prerequis').addEventListener('trix-blur', this.savePrerequis.bind(this))
    document.getElementById('parcours_step5_coordSecretariat').addEventListener('trix-blur', this.coordSecretariat.bind(this))
    document.getElementById('parcours_step5_modalitesAlternance').addEventListener('trix-blur', this.saveModalitesAlternance.bind(this))
    this._checkIfAlternance()
  }

  coordSecretariat() {
    this._save({
      field: 'coordSecretariat',
      action: 'textarea',
      value: trixEditor('parcours_step5_coordSecretariat'),
    })
  }

  etatStep(event) {
    calculEtatStep(this.urlValue, 5, event, 'parcours')
  }

  savePrerequis() {
    this._save({
      field: 'prerequis',
      action: 'textarea',
      value: trixEditor('parcours_step5_prerequis'),
    })
  }

  changeRegimeInscription(event) {
    this._save({
      action: 'array',
      field: 'regimeInscription',
      value: event.target.value,
      isChecked: event.target.checked,
    })
    this._checkIfAlternance()
  }

  changeComposanteInscription(event) {
    this._save({
      action: 'composanteInscription',
      value: event.target.value,
    })
  }

  _checkIfAlternance() {
    let hasAlternance = false

    document.querySelectorAll('input[name="parcours_step5[regimeInscription][]"]').forEach((element) => {
      if (element.checked) {
        if (element.value === 'Formation Initiale en apprentissage' || element.value === 'Formation Continue Contrat Professionnalisation') {
          hasAlternance = true
        }
      }
    })

    if (hasAlternance) {
      document.getElementById('parcours_step5_modalitesAlternance').disabled = false
    } else {
      document.getElementById('parcours_step5_modalitesAlternance').disabled = true
    }
  }

  saveModalitesAlternance() {
    this._save({
      field: 'modalitesAlternance',
      action: 'textarea',
      value: trixEditor('parcours_step5_modalitesAlternance'),
    })
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet5', 'parcours')
    })
  }
}
