/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/step3_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/03/2023 21:15
 */

import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { calculEtatStep } from '../../js/calculEtatStep'
import trixEditor from '../../js/trixEditor'
import JsonResponse from '../../js/JsonResponse'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
    urlUpdate: String,
    updateStep: { type: Boolean, default: true },
  }

  connect() {
    // if (document.getElementById('fiche_matiere_step3_objectifs')) {
    //   document.getElementById('fiche_matiere_step3_objectifs').addEventListener('trix-blur', this.saveObjectifs.bind(this))
    // }
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

  changeCompetence(event) {
    this._save({ action: 'addCompetence', value: event.params.id, checked: event.target.checked })
  }

  etatStep(event) {
    calculEtatStep(this.urlValue, 3, event, 'ec')
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      if (this.updateStepValue) {
        await updateEtatOnglet(this.urlValue, 'onglet3', 'ec')
      }
    })
  }

  async synchroBcc(event) {
    const body = new FormData()
    body.append('value', event.target.checked)
    body.append('field', 'synchroBcc')
    body.append('ec', event.params.ec)

    await fetch(this.urlUpdateValue, {
      method: 'POST',
      body,
    }).then((response) => {
      if (response.ok) {
        // récupérer tous les inputs, sauf ceux ayant la classe "not-disabled" et les désactiver ou activer selon la valeur du checkbox

        // récupérer tous les checkbox dont le nom commence par bcc_ et les désactiver ou activer selon la valeur du checkbox
        const inputs = document.querySelectorAll('[name^="ec["]')
        inputs.forEach((input) => {
          if (event.target.checked) {
            input.setAttribute('disabled', 'disabled')
          } else {
            input.removeAttribute('disabled')
          }
        })
      }
      JsonResponse(response)
    })
  }
}
