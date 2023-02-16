import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'
import callOut from '../js/callOut'

export default class extends Controller {
  static values = {
    urlListe: String,
    urlAdd: String,
  }

  static targets = ['liste']

  connect() {
    this._updateListe()
  }

  async _updateListe() {
    this.listeTarget.innerHTML = 'Chargement...'
    fetch(this.urlListeValue)
      .then((response) => response.text())
      .then((html) => {
        this.listeTarget.innerHTML = html
      })
  }

  addCentre(event) {
    event.preventDefault()
    fetch(this.urlAddValue, {
      method: 'POST',
      body: JSON.stringify({
        centreType: document.getElementById('typeCentre').value,
        centreId: document.getElementById('selectListe').value,
        role: document.getElementById('droits').value,
      }),
    })
      .then((response) => response.json())
      .then((json) => {
        if (json.success) {
          callOut('Centre ajouté', 'success')
          this._updateListe()
        } else {
          callOut(json.error, 'error')
        }
      })
  }

  delete(event) {
    event.preventDefault()
    const { url } = event.params
    const { csrf } = event.params
    let modal = new Modal(document.getElementById('modal-delete'))
    modal.show()
    document.getElementById('btn-confirm-supprimer').addEventListener('click', async () => {
      const body = {
        method: 'DELETE',
        body: JSON.stringify({
          csrf,
        }),
      }
      modal = null
      await fetch(url, body).then(() => {
        callOut('Suppression effectuée', 'success')
        this._updateListe()
      })
    })
  }
}
