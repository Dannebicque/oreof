import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'
import { addCallout } from '../../js/callOut'

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

  delete(event) {
    event.preventDefault()
    const url = event.params.url
    const csrf = event.params.csrf
    let modal = new Modal(document.getElementById('modal-delete'))
    modal.show()
    document.getElementById('btn-confirm-supprimer').addEventListener('click', async (event) => {
      const body = {
        method: 'DELETE',
        body: JSON.stringify({
          csrf: csrf,
        }),
      }
      modal = null
      await fetch(url, body).then((response) => {
        addCallout('Suppression effectuée', 'success')
        this._updateListe()
      })

    })
  }

  async duplicate(event) {
    event.preventDefault()
    const url = event.params.url
    await fetch(url).then((response) => {
      addCallout('Duplication effectuée', 'success')
      this._updateListe()
    })
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
