import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {
  static targets = ['detail']

  static values = {
    url: String,
    urlSave: String,
  }

  connect() {
    this._loadParcours()
  }

  async _loadParcours() {
    const response = await fetch(this.urlValue)
    this.detailTarget.innerHTML = await response.text()
  }

  etatStructure(event) {
    saveData(this.urlSaveValue, {
      action: 'etatStructure',
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
}
