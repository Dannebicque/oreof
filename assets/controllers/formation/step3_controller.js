import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = [
    'liste',
  ]
  static values = {
    url: String,
    urlListe: String,
  }

  connect() {
    this._updateListe()
  }
  refreshListe() {
    this._updateListe()
  }

  async _updateListe() {
    this.listeTarget.innerHTML = window.da.loaderStimulus
    const response = await fetch(this.urlListeValue)
    this.listeTarget.innerHTML = await response.text()
  }
}
