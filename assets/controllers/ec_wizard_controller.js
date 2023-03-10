import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = [
    'content',
    'synthese',
  ]

  static values = {
    url: String,
    urlSynthese: String,
    ec: String,
    parcours: String,
  }

  connect() {
    this._loadSynthese()
    this._loadStep(1)
  }

  refreshStep() {
    this._loadStep(2)
  }

  async _loadSynthese() {
    const response = await fetch(this.urlSyntheseValue)
    this.syntheseTarget.innerHTML = await response.text()
  }

  refreshSynthese() {
    this._loadSynthese()
  }

  changeStep(event) {
    this._loadStep(event.params.step)
  }

  async _loadStep(step) {
    const response = await fetch(`${this.urlValue + this.ecValue}/${this.parcoursValue}/${step}`)
    this.contentTarget.innerHTML = await response.text()
  }
}
