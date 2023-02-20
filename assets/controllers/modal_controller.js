// Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
// @file /Users/davidannebicque/htdocs/intranetV3/assets/controllers/modal_controller.js
// @author davidannebicque
// @project intranetV3
// @lastUpdate 11/10/2021 21:49

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    modalUrl: String,
    nomEvenement: { type: String, default: 'refreshListe' },
    modalTitle: String,
    formAction: String,
    size: { type: String, default: 'md' },
    btnClose: { type: String, default: 'Fermer' },
    form: { type: Boolean, default: false },
    params: Array,
  }

  openModal(event) {
    console.log(this.nomEvenementValue)
    event.preventDefault()
    this.dispatch('openModal', {
      detail: {
        url: this.modalUrlValue,
        formAction: this.formActionValue,
        form: this.formValue,
        nomEvenement: this.nomEvenementValue,
        size: this.sizeValue,
        btnClose: this.btnCloseValue,
        params: this.paramsValue,
        title: this.modalTitleValue,
      },
    })
  }
}
