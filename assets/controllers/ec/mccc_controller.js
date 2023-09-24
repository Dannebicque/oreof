/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/structure_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 13/03/2023 21:49
 */

import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { calculEtatStep } from '../../js/calculEtatStep'
import JsonResponse from '../../js/JsonResponse'

export default class extends Controller {
  static values = {
    urlUpdate: String,
  }

  async synchroEcts(event) {
    const body = new FormData()
    body.append('value', event.target.checked)
    body.append('field', 'synchroEcts')
    body.append('ec', event.params.ec)

    await fetch(this.urlUpdateValue, {
      method: 'POST',
      body,
    }).then((response) => {
      if (response.ok) {
        const input = document.getElementById('ec_step4_ects')

        if (event.target.checked) {
          input.setAttribute('disabled', 'disabled')
        } else {
          input.removeAttribute('disabled')
        }
      }
      JsonResponse(response)
    })
  }

  async synchroMccc(event) {
    const body = new FormData()
    body.append('value', event.target.checked)
    body.append('field', 'synchroMccc')
    body.append('ec', event.params.ec)

    await fetch(this.urlUpdateValue, {
      method: 'POST',
      body,
    }).then((response) => {
      if (response.ok) {
        // récupérer tous les inputs, sauf ceux ayant la classe "not-disabled" et les désactiver ou activer selon la valeur du checkbox
        const inputs = document.querySelectorAll('.synchro-mccc')
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
