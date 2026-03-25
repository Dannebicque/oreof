/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/modal_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 14:53
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['zoneToRefresh'];

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

  async refreshModalWithUrl(event) {
    const errorText = '<div class="text-center">Une erreur est survenue lors du chargement.</div>'
    const { url } = event.params
    this.zoneToRefreshTarget.innerHTML = '<div class="text-center">... Chargement en cours ...</div>';
    await fetch(url)
      .then(async (response) => {
        if (response.status === 500) {
          this.zoneToRefreshTarget.innerHTML = errorText;
        } else if (response.status === 200) {
          await response.text()
            .then((responseText) => this.zoneToRefreshTarget.innerHTML = responseText);
        }
      })
      .catch(err => {
        console.log(err);
        this.zoneToRefreshTarget.innerHTML = errorText
      });
  }
}
