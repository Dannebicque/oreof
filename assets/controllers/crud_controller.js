import { Controller } from '@hotwired/stimulus'
import { useDispatch } from 'stimulus-use'
import { Modal } from 'bootstrap'
import callOut from '../js/callOut'

export default class extends Controller {
  static targets = ['liste']

  static values = { url: String }

  connect() {
    this._updateListe()
  }

  delete(event) {
    event.preventDefault()
    const { url } = event.params
    const { csrf } = event.params
    let modal = new Modal(document.getElementById('modal-delete'))
    modal.show()
    document.getElementById('btn-confirm-supprimer').addEventListener('click', async (event) => {
      const body = {
        method: 'DELETE',
        body: JSON.stringify({
          csrf,
        }),
      }
      modal = null
      await fetch(url, body).then((response) => {
        callOut('Suppression effectuée', 'success')
        this._updateListe()
      })
    })
  }

  async duplicate(event) {
    event.preventDefault()
    const { url } = event.params
    await fetch(url).then((response) => {
      callOut('Duplication effectuée', 'success')
      this._updateListe()
    })
  }

  refreshListe() {
    this._updateListe()
  }

  async _updateListe() {
    this.listeTarget.innerHTML = window.da.loaderStimulus
    const response = await fetch(this.urlValue)
    this.listeTarget.innerHTML = await response.text()
  }
}
