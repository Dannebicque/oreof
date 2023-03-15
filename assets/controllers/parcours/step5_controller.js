import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { calculEtatStep } from '../../js/calculEtatStep'
import trixEditor from '../../js/trixEditor'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
  }

  connect() {
    document.getElementById('parcours_step5_prerequis').addEventListener('trix-blur', this.savePrerequis.bind(this))
  }

  etatStep(event) {
    calculEtatStep(this.urlValue, 5, event, 'parcours')
  }

  savePrerequis() {
    this._save({
      field: 'prerequis',
      action: 'textarea',
      value: trixEditor('parcours_step5_prerequis'),
    })
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet5', 'parcours')
    })
  }
}
