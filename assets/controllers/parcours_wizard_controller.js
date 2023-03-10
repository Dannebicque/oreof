import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
    step: String,
    parcours: String,
  }

  connect() {
    this._loadStep(this.stepValue)
  }

  changeStep(event) {
    this._loadStep(event.params.step)
  }

  async _loadStep(step) {
    const response = await fetch(`${this.urlValue + this.parcoursValue}/${step}`)
    this.contentTarget.innerHTML = await response.text()
  }
}
