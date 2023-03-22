/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/formation/step1_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/03/2023 21:03
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
    document.getElementById('formation_step1_modalitesAlternance').addEventListener('trix-blur', this.saveModalitesAlternance.bind(this))
    this._checkIfAlternance()
  }

  changeVille(event) {
    this._save({
      action: 'ville',
      value: event.target.value,
      isChecked: event.target.checked,
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

  _checkIfAlternance() {
    let hasAlternance = false
    document.querySelectorAll('input[name="formation_step1[regimeInscription][]"]').forEach((element) => {
      if (element.checked) {
        if (element.value === 'Formation Initiale en apprentissage' || element.value === 'Formation Continue Contrat Professionnalisation') {
          hasAlternance = true
        }
      }
    })

    // const trix = document.getElementById('formation_step1_modalitesAlternance')
    // const _trixEditor = trix.editor
    // _trixEditor.element.disabled = true
    // console.log(_trixEditor)

    document.getElementById('formation_step1_modalitesAlternance').disabled = !hasAlternance;
  }

  changeComposanteInscription(event) {
    this._save({
      action: 'composanteInscription',
      value: event.target.value,
      isChecked: event.target.checked,
    })
  }

  saveModalitesAlternance() {
    this._save({
      field: 'modalitesAlternance',
      action: 'textarea',
      value: trixEditor('formation_step1_modalitesAlternance'),
    })
  }

  async etatStep(event) {
    event.preventDefault()
    await calculEtatStep(this.urlValue, 1, event, 'formation')
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet1', 'formation')
    })
  }
}
