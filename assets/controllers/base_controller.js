/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/base_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 14:53
 */

import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'

import TomSelect from 'tom-select';
import callOut from '../js/callOut'

export default class extends Controller {
  static targets = ['modal', 'modalBody', 'modalTitle', 'size', 'btnClose', 'liste']

  modal = null

  nomEvenement = 'refreshListe'

  details = {}

  sauvegardeFormModal(event) {
    event.preventDefault()

    const form = this.element.getElementsByTagName('form')[0]
    fetch(form.action, {
      method: form.method,
      body: new URLSearchParams(new FormData(form)),
    })
      .then((response) => response.json())
      .then(async () => {
        callOut('Sauvegarde effectuée', 'success')
        this.modal.hide()
        if (this.updateComponent) {
          const component = document.getElementById(this.updateComponent.id).__component;
          component.emit(this.updateComponent.event);
        }
        this.dispatch(this.nomEvenement, { detail: this.details })
      })
  }

  async openModal(event) {
    this.modalBodyTarget.innerHTML = window.da.loaderStimulus
    this.modalTitleTarget.innerHTML = event.detail.title
    this.btnCloseTarget.innerHTML = event.detail.btnClose
    this.nomEvenement = event.detail.nomEvenement
    this.updateComponent = event.detail.updateComponent
    this.details = event.detail.details
    if (event.detail.form === true) {
      document.getElementById('btn_modal_submit').classList.remove('d-none')
      this.element.getElementsByTagName('form')[0].action = event.detail.formAction
    }

    if (event.detail.right === true) {
      document.getElementById('stimulus_modal').parentElement.classList.add('modal-right', event.detail.size)
    } else {
      document.getElementById('stimulus_modal').parentElement.classList.remove('modal-right')
      document.getElementById('stimulus_modal').classList.add(`modal-${event.detail.size}`)
    }

    this.modal = new Modal(this.modalTarget)
    this.modal.show()
    let response = null
    if (event.detail.params.lenth > 0) {
      const params = new URLSearchParams(event.detail.params)
      response = await fetch(`${event.detail.url}?${params.toString()}`)
    } else {
      response = await fetch(`${event.detail.url}`)
    }
    this.modalBodyTarget.innerHTML = await response.text()
  }

  modalHide() {
    this.modal.hide()
    this.modalBodyTarget.innerHTML = ''
    document.querySelectorAll('.modal-backdrop').forEach((e) => {
      e.remove()
    })
    this.dispatch('modalClose')
  }

  modalClose() {
    this.modalBodyTarget.innerHTML = ''
    document.querySelectorAll('.modal-backdrop').forEach((e) => {
      e.remove()
    })
    this.dispatch('modalClose')
  }

  async refreshModale(event) {
    this.modalBodyTarget.innerHTML = ''
    const response = await fetch(`${event.detail.url}`)
    this.modalBodyTarget.innerHTML = await response.text()
  }
}
