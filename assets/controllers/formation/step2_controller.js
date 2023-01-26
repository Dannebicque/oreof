import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {
  static targets = [
    'content',
  ]
  static values = {
    url: String,
  }

  saveContenu(event) {
    saveData(this.urlValue, {
      field: 'contenuFormation',
      action: 'textarea',
      value: document.getElementById('formation_step2_contenuFormation').value,
    })
  }

  saveResultats(event) {
    saveData(this.urlValue, {
      field: 'resultatsAttendus',
      action: 'textarea',
      value: document.getElementById('formation_step2_resultatsAttendus').value,
    })
  }

  changeRythme(event) {
    saveData(this.urlValue, {
      field: 'rythmeFormation',
      action: 'rythmeFormation',
      value: event.target.value,
    })
  }

  saveRythme(event) {
    saveData(this.urlValue, {
      field: 'rythmeFormationTexte',
      action: 'textarea',
      value: document.getElementById('formation_step2_rythmeFormationTexte').value,
    })
  }
}
