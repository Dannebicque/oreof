/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/modal_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 14:53
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    modalUrl: String,
    nomEvenement: { type: String, default: 'refreshListe' },
    details: { type: Object, default: {} },
    modalTitle: String,
    formAction: String,
    size: { type: String, default: 'md' },
    right: { type: Boolean, default: false },
    btnClose: { type: String, default: 'Fermer' },
    form: { type: Boolean, default: false },
    params: Array,
    updateComponent: { type: Object, default: {} },
  }

  openModal(event) {
    event.preventDefault()
    this.dispatch('openModal', {
      detail: {
        url: this.modalUrlValue,
        formAction: this.formActionValue,
        form: this.formValue,
        nomEvenement: this.nomEvenementValue,
        details: this.detailsValue,
        size: this.sizeValue,
        btnClose: this.btnCloseValue,
        params: this.paramsValue,
        title: this.modalTitleValue,
        right: this.rightValue,
        updateComponent: this.updateComponentValue,
      },
    })
  }
}
