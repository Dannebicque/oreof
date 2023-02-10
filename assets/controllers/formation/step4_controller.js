import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['detail']

  static values = {
    url: String,
  }

  connect() {
    this._loadParcours()
  }

  async _loadParcours() {
    const response = await fetch(this.urlValue)
    this.detailTarget.innerHTML = await response.text()
  }
}
