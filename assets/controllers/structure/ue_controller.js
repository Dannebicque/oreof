import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {
  static targets = ['detail']

  async detail(event) {
    this._listeEc(event)
  }

  async refreshListeEc(event) {
    this._listeEc(event)
  }

  async _listeEc(event) {
    const response = await fetch(event.params.url)
    this.detailTarget.innerHTML = await response.text()
    document.getElementById(`detail_ue_${event.params.ue}`).classList.remove('d-none')
  }

  changeUeObligatoire(event) {
    saveData(event.params.url, {
      actions: 'changeUeObligatoire',
      value: event.target.value,
    })
  }

  changeTypeUe(event) {
    saveData(event.params.url, {
      actions: 'changeTypeUe',
      value: event.target.value,
    })
  }
}
