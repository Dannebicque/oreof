import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = [
    'content',
  ]
  static values = {
    url: String,
    ec: String,
  }

  connect() {
    this._loadStep(1)
  }

  async changeStep(event) {
    this._loadStep(event.params.step)
  }

  async _loadStep(step) {
    const response = await fetch(this.urlValue + this.ecValue + "/" + step)
    this.contentTarget.innerHTML = await response.text()
  }

}
