import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { etatStep } from '../../js/etatStep'

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

  etatStep(event) {
    etatStep(this.urlValue, 1, event, 'formation')

    // this._save({
    //   action: 'etatStep',
    //   value: 1,
    //   isChecked: event.target.checked,
    // })
    //
    // const parent = event.target.closest('.alert')
    // if (event.target.checked) {
    //   parent.classList.remove('alert-warning')
    //   parent.classList.add('alert-success')
    // } else {
    //   parent.classList.remove('alert-success')
    //   parent.classList.add('alert-warning')
    // }
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet1', 'formation')
    })
  }
}
