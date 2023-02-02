import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {
  static targets = [
    'content',
  ]
  static values = {
    url: String,
  }

  saveObjectifs(event) {
    saveData(this.urlValue, {
      field: 'objectifs',
      action: 'textarea',
      value: document.getElementById('ec_step3_objectifs').value,
    })
  }
}
