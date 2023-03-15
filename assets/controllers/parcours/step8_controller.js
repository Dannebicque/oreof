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
    document.getElementById('parcours_step8_coordSecretariat').addEventListener('trix-blur', this.coordSecretariat.bind(this))
  }

  respParcours() {
    this._save({
      action: 'respParcours',
      value: document.getElementById('parcours_step8_respParcours').value,
    })
  }

  coordSecretariat() {
    this._save({
      field: 'coordSecretariat',
      action: 'textarea',
      value: trixEditor('parcours_step8_coordSecretariat'),
    })
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet8', 'parcours')
    })
  }

  etatStep(event) {
    calculEtatStep(this.urlValue, 8, event, 'parcours')
  }
}
