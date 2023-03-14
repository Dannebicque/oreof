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

  etatStep(event) {
    calculEtatStep(this.urlValue, 1, event, 'parcours')
  }

  saveContenu() {
    this._save({
      field: 'contenuFormation',
      action: 'textarea',
      value: document.getElementById('parcours_step1_contenuFormation').value,
    })
  }

  saveObjectifsParcours() {
    this._save({
      field: 'objectifsParcours',
      action: 'textarea',
      value: document.getElementById('parcours_step1_objectifsParcours').value,
    })
  }

  saveResultats() {
    this._save({
      field: 'resultatsAttendus',
      action: 'textarea',
      value: document.getElementById('parcours_step1_resultatsAttendus').value,
    })
  }

  changeRythme(event) {
    this._save({
      field: 'rythmeFormation',
      action: 'rythmeFormation',
      value: event.target.value,
    })
  }

  changeLocalisation(event) {
    this._save({
      field: 'localisation',
      action: 'localisation',
      value: event.target.value,
    })
  }

  saveRythme() {
    this._save({
      field: 'rythmeFormationTexte',
      action: 'textarea',
      value: document.getElementById('parcours_step1_rythmeFormationTexte').value,
    })
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet1', 'parcours')
    })
  }
}
