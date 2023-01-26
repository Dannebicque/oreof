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
    console.log('step 1')
  }

  changeSite(event) {
    this._save({
      action: 'site',
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
  }

  changeComposanteInscription(event) {
    this._save({
      action: 'composanteInscription',
      value: event.target.value,
      isChecked: event.target.checked,
    })
  }

  saveModalitesAlternance(event) {
    this._save({
      field: 'modalitesAlternance',
      action: 'textarea',
      value: document.getElementById('formation_step1_modalitesAlternance').value,
    })
  }

  _save(options) {
    saveData(this.urlValue, options)
  }
}
