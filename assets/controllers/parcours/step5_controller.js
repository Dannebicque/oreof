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
    calculEtatStep(this.urlValue, 5, event, 'parcours')
  }

  savePrerequis() {
    this._save({
      field: 'prerequis',
      action: 'textarea',
      value: document.getElementById('parcours_step5_prerequis').value,
    })
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet5', 'parcours')
    })
  }
}
