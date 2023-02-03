import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
  }

  respParcours() {
    saveData(this.urlValue, {
      action: 'respParcours',
      value: document.getElementById('parcours_step8_respParcours').value,
    })
  }

  coordSecretariat() {
    saveData(this.urlValue, {
      field: 'coordSecretariat',
      action: 'textarea',
      value: document.getElementById('parcours_step8_coordSecretariat').value,
    })
  }
}
