import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
  }

  connect() {
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

    if (hasAlternance) {
      document.getElementById('formation_step1_modalitesAlternance').disabled = false
    } else {
      document.getElementById('formation_step1_modalitesAlternance').disabled = true
    }
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
      value: document.getElementById('formation_step1_modalitesAlternance').value,
    })
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet1', 'formation')
    })
  }
}
