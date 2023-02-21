import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
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
      value: document.getElementById('parcours_step8_coordSecretariat').value,
    })
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet8', 'parcours')
    })
  }
}
