import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import callOut from '../../js/callOut'
import { calculEtatStep } from '../../js/calculEtatStep'

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
    calculEtatStep(this.urlValue, 7, event, 'parcours')
  }
}
