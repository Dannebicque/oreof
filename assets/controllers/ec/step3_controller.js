import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import callOut from '../../js/callOut'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
  }

  saveObjectifs() {
    saveData(this.urlValue, {
      field: 'objectifs',
      action: 'textarea',
      value: document.getElementById('ec_step3_objectifs').value,
    })
  }

  changeBcc(event) {
    if (event.target.checked) {
      document.getElementById(`bcc_${event.params.id}_competence`).classList.remove('d-none')
    } else {
      document.getElementById(`bcc_${event.params.id}_competence`).classList.add('d-none')
      document.querySelectorAll(`.bcc_${event.params.id}`).forEach((element) => {
        element.checked = false
      })
      saveData(this.urlValue, { action: 'removeBcc', value: event.params.id })
    }
  }

  changeCompetence(event) {
    saveData(this.urlValue, { action: 'addCompetence', value: event.params.id, checked: event.target.checked })
  }
}
