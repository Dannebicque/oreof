import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
    formation: String,
    step: String,
  }

  connect() {
    this._loadStep(this.stepValue)
  }

  async changeStep(event) {
    this._loadStep(event.params.step)
  }

  async changeStepParcours(event) {
    this._loadStepParcours(event.params.step, event.params.parcours)
  }

  async _loadStep(step) {
    const response = await fetch(`${this.urlValue + this.formationValue}/${step}`)
    this.contentTarget.innerHTML = await response.text()
  }

  async _loadStepParcours(step, parcours) {
    const response = await fetch(`${this.urlValue + this.formationValue}/${parcours}/${step}`)
    this.contentTarget.innerHTML = await response.text()
  }
}
