import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
  }

  saveDescription() {
    saveData(this.urlValue, {
      field: 'description',
      action: 'textarea',
      value: document.getElementById('ec_step2_description').value,
    })
  }

  changeLangue(event) {
    saveData(
      this.urlValue,
      {
        field: event.params.type,
        action: 'langue',
        value: event.target.value,
        isChecked: event.target.checked,
      },
    )
  }

  changeTypeEnseignement(event) {
    saveData(
      this.urlValue,
      {
        action: 'typeEnseignement',
        value: event.target.value,
      },
    )
  }
}
