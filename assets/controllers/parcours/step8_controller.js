import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { etatStep } from '../../js/etatStep'

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

  etatStep(event) {
    etatStep(this.urlValue, 8, event, 'parcours')

    // this._save({
    //   action: 'etatStep',
    //   value: 8,
    //   isChecked: event.target.checked,
    // })
    //
    // const parent = event.target.closest('.alert')
    // if (event.target.checked) {
    //   parent.classList.remove('alert-warning')
    //   parent.classList.add('alert-success')
    // } else {
    //   parent.classList.remove('alert-success')
    //   parent.classList.add('alert-warning')
    // }
  }
}
