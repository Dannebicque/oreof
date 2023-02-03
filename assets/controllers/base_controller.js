import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'
import callOut from '../js/callOut'

export default class extends Controller {
  static targets = ['modal', 'modalBody', 'modalTitle', 'size', 'btnClose', 'liste']

  modal = null

  sauvegardeFormModal(event) {
    event.preventDefault()
    const zone = document.getElementById('liste')

    const form = this.element.getElementsByTagName('form')[0]
    fetch(form.action, {
      method: form.method,
      body: new URLSearchParams(new FormData(form)),
    })
      .then((response) => response.json())
      .then(async (data) => {
        callOut('Sauvegarde effectuée', 'success')
        this.modal.hide()
        this.dispatch('refreshListe')
      })
  }

  async openModal(event) {
    this.modalBodyTarget.innerHTML = window.da.loaderStimulus
    this.modalTitleTarget.innerHTML = event.detail.title
    this.btnCloseTarget.innerHTML = event.detail.btnClose

    if (event.detail.form === true) {
      document.getElementById('btn_modal_submit').classList.remove('d-none')
      this.element.getElementsByTagName('form')[0].action = event.detail.formAction
    }

    document.getElementById('stimulus_modal').classList.add(`modal-${event.detail.size}`)

    this.modal = new Modal(this.modalTarget)
    this.modal.show()

    const params = new URLSearchParams(event.detail.params)
    const response = await fetch(`${event.detail.url}?${params.toString()}`)
    this.modalBodyTarget.innerHTML = await response.text()
  }

  modalClose() {
    // todo: déclencher sur l'évent de Bootstrap?
    this.modalBodyTarget.innerHTML = ''
  }
}
