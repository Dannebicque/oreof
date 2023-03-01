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

  saveDescription() {
    this._save({
      field: 'description',
      action: 'textarea',
      value: document.getElementById('ec_step2_description').value,
    })
  }

  changeLangue(event) {
    this._save({
      field: event.params.type,
      action: 'langue',
      value: event.target.value,
      isChecked: event.target.checked,
    })
  }

  changeTypeEnseignement(event) {
    this._save({
      action: 'typeEnseignement',
      value: event.target.value,
    })
  }

  etatStep(event) {
    this._save({
      action: 'etatStep',
      value: 2,
      isChecked: event.target.checked,
    })

    const parent = event.target.closest('.alert')
    if (event.target.checked) {
      parent.classList.remove('alert-warning')
      parent.classList.add('alert-success')
    } else {
      parent.classList.remove('alert-success')
      parent.classList.add('alert-warning')
    }
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet2', 'ec')
    })
  }
}
