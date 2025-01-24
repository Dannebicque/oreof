/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/parcours/bcc_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/04/2023 16:41
 */
import { Controller } from '@hotwired/stimulus'
import callOut from '../../js/callOut'
import JsonResponse from '../../js/JsonResponse'

export default class extends Controller {
  static values = {
    urlUpdateComptence: String,
  }

  changeBcc(event) {
    if (event.target.checked) {
      document.getElementById(`bcc_${event.params.id}_competence`).classList.remove('d-none')
    } else {
      document.getElementById(`bcc_${event.params.id}_competence`).classList.add('d-none')
      document.querySelectorAll(`.bcc_${event.params.id}`).forEach((element) => {
        element.checked = false
      })
      this._save({ action: 'removeBcc', value: event.params.id })
    }
  }

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
        this.dispatch('modalClose')
      })
  }

  async updateCompetence(event) {
    const body = new FormData()
    body.append('competence', event.params.competence)
    body.append('ec', event.params.ec)
    body.append('checked', event.currentTarget.checked)

    await fetch(this.urlUpdateComptenceValue, {
      method: 'POST',
      body,
    }).then((response) => {
      // response.json()
      JsonResponse(response)
    })
  }
}
