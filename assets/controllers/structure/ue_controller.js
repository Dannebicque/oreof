import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'
import { saveData } from '../../js/saveData'
import callOut from '../../js/callOut'

export default class extends Controller {
  static targets = ['detail']

  detail(event) {
    if (event.target.dataset.state === 'open') {
      document.getElementById(`detail_ue_${event.params.ue}_${event.params.parcours}`).classList.add('d-none')
      this.detailTarget.innerHTML = ''
      event.target.dataset.state = 'close'
      event.target.firstElementChild.classList.add('fa-caret-right')
      event.target.firstElementChild.classList.remove('fa-caret-down')
    } else {
      this._listeEc(event)
      event.target.dataset.state = 'open'
      event.target.firstElementChild.classList.remove('fa-caret-right')
      event.target.firstElementChild.classList.add('fa-caret-down')
    }
  }

  refreshListeEc(event) {
    this._listeEc(event)
  }

  async _listeEc(event) {
    console.log(`detail_ue_${event.params.ue}_${event.params.parcours}`)
    const response = await fetch(event.params.url)
    this.detailTarget.innerHTML = await response.text()
    document.getElementById(`detail_ue_${event.params.ue}_${event.params.parcours}`).classList.remove('d-none')
  }

  changeUeObligatoire(event) {
    saveData(event.params.url, {
      actions: 'changeUeObligatoire',
      value: event.target.value,
    })
  }

  changeTypeUe(event) {
    saveData(event.params.url, {
      actions: 'changeTypeUe',
      value: event.target.value,
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
        this.dispatch('refreshListe')// todo: ne marche pas ??
      })
    })
  }
}
