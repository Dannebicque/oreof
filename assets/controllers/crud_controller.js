import { Controller } from '@hotwired/stimulus'
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
    const btn = document.getElementById('btn-confirm-supprimer')
    btn.replaceWith(btn.cloneNode(true));
    document.getElementById('btn-confirm-supprimer').addEventListener('click', async () => {
      const body = {
        method: 'DELETE',
        body: JSON.stringify({
          csrf,
        }),
      }
      modal = null
      await fetch(url, body).then((e) => {
        if (e.status === 200) {
          callOut('Suppression effectuée', 'success')
          this._updateListe()
        } else {
          callOut('Erreur lors de la suppression', 'danger')
        }
      })
    })
    modal = null
  }

  async duplicate(event) {
    event.preventDefault()
    const { url } = event.params
    await fetch(url).then(() => {
      callOut('Duplication effectuée', 'success')
      this._updateListe()
    })
  }

  refreshListe() {
    this._updateListe()
  }

  async _updateListe(params) {
    const _params = new URLSearchParams(params)
    this.listeTarget.innerHTML = window.da.loaderStimulus
    const response = await fetch(`${this.urlValue}?${_params.toString()}`)
    this.listeTarget.innerHTML = await response.text()
  }

  sort(event) {
    this._updateListe(event.params)
  }
}
