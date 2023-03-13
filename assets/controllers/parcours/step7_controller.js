import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import callOut from '../../js/callOut'
import { etatStep } from '../../js/etatStep'

export default class extends Controller {
  static targets = [
    'listeCodes',
  ]

  static values = {
    url: String,
    urlCodeRome: String,
    urlCodeRomeGere: String,
  }

  connect() {
    this._loadRome()
  }

  async removeCode(event) {
    event.preventDefault()
    const body = {
      method: 'POST',
      body: JSON.stringify({
        action: 'DELETE',
        code: event.params.code,
      }),
    }

    await fetch(this.urlCodeRomeGereValue, body).then((response) => response.json()).then((data) => {
      if (data === true) {
        callOut('Code supprimé', 'success')
        this._loadRome()
      } else {
        callOut('Erreur lors de la suppression', 'danger')
      }
    })
  }

  async addCode(event) {
    event.preventDefault()
    const body = {
      method: 'POST',
      body: JSON.stringify({
        action: 'ADD',
        code: document.getElementById('codeRomeToAdd').value,
      }),
    }

    await fetch(this.urlCodeRomeGereValue, body).then((response) => response.json()).then((data) => {
      if (data === true) {
        callOut('Code ajouté', 'success')
        this._loadRome()
      } else {
        callOut('Erreur lors de la sauvegarde', 'danger')
      }
    })
  }

  async _loadRome() {
    this.listeCodesTarget.innerHTML = window.da.loaderStimulus
    const response = await fetch(this.urlCodeRomeValue)
    this.listeCodesTarget.innerHTML = await response.text()
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet7', 'parcours')
    })
  }

  savePoursuitesEtudes() {
    this._save({
      field: 'poursuitesEtudes',
      action: 'textarea',
      value: document.getElementById('parcours_step7_poursuitesEtudes').value,
    })
  }

  saveDebouches() {
    this._save({
      field: 'debouches',
      action: 'textarea',
      value: document.getElementById('parcours_step7_debouches').value,
    })
  }

  etatStep(event) {
    etatStep(this.urlValue, 7, event, 'parcours')
    // this._save({
    //   action: 'etatStep',
    //   value: 7,
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
}
