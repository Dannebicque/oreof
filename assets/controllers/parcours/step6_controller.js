import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

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

    document.querySelectorAll('input[name="parcours_step6[regimeInscription][]"]').forEach((element) => {
      if (element.checked) {
        if (element.value === 'Formation Initiale en apprentissage' || element.value === 'Formation Continue Contrat Professionnalisation') {
          hasAlternance = true
        }
      }
    })

    if (hasAlternance) {
      document.getElementById('parcours_step6_modalitesAlternance').disabled = false
    } else {
      document.getElementById('parcours_step6_modalitesAlternance').disabled = true
    }
  }

  saveModalitesAlternance() {
    this._save({
      field: 'modalitesAlternance',
      action: 'textarea',
      value: document.getElementById('parcours_step6_modalitesAlternance').value,
    })
  }

  _save(options) {
    saveData(this.urlValue, options)
  }
}
