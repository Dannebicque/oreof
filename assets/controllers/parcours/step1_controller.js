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

  etatStep(event) {
    etatStep(this.urlValue, 1, event, 'parcours')
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

  saveContenu() {
    this._save({
      field: 'contenuFormation',
      action: 'textarea',
      value: document.getElementById('parcours_step1_contenuFormation').value,
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
