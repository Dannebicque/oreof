import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {
  static targets = [
    'content',
  ]
  static values = {
    url: String,
  }

  savePrerequis(event) {
    saveData(this.urlValue, {
      field: 'prerequis',
      action: 'textarea',
      value: document.getElementById('formation_step5_prerequis').value,
    })
  }

  connect() {
  }
}
