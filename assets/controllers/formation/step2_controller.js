import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { calculEtatStep } from '../../js/calculEtatStep'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
  }

  saveContenu() {
    this._save({
      field: 'contenuFormation',
      action: 'textarea',
      value: document.getElementById('formation_step2_contenuFormation').value,
    })
  }

  saveResultats() {
    this._save({
      field: 'resultatsAttendus',
      action: 'textarea',
      value: document.getElementById('formation_step2_resultatsAttendus').value,
    })
  }

  changeRythme(event) {
    this._save({
      field: 'rythmeFormation',
      action: 'rythmeFormation',
      value: event.target.value,
    })
  }

  saveRythme() {
    this._save({
      field: 'rythmeFormationTexte',
      action: 'textarea',
      value: document.getElementById('formation_step2_rythmeFormationTexte').value,
    })
  }

  saveObjectifsFormation() {
    this._save({
      field: 'objectifsFormation',
      action: 'textarea',
      value: document.getElementById('formation_step2_objectifsFormation').value,
    })
  }

  etatStep(event) {
    calculEtatStep(this.urlValue, 2, event, 'formation')
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet2', 'formation')
    })
  }
}
