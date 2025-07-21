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

  async ectsSpecifiques(event) {
    const body = new FormData()
    body.append('value', event.target.checked)
    body.append('field', 'ectsSpecifiques')
    body.append('ec', event.params.ec)

    await fetch(this.urlUpdateValue, {
      method: 'POST',
      body,
    }).then((response) => {
      if (response.ok) {
        const input = document.getElementById('ec_step4_ects')

        if (event.target.checked) {
          input.removeAttribute('disabled')
        } else {
          input.setAttribute('disabled', 'disabled')
        }
      }
      JsonResponse(response)
    })
  }

  async controleAssiduite(event) {
    const body = new FormData()
    body.append('value', event.target.checked)
    body.append('field', 'controleAssiduite')
    body.append('ec', event.params.ec)

    await fetch(this.urlUpdateValue, {
      method: 'POST',
      body,
    }).then((response) => {
      if (response.ok) {
        if (event.target.checked) {
          document.getElementById('choix_type_mccc').setAttribute('disabled', 'disabled')
        } else {
          document.getElementById('choix_type_mccc').removeAttribute('disabled')
        }

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

  async mcccSpecifiques(event) {
    const body = new FormData()
    body.append('value', event.target.checked)
    body.append('field', 'mcccSpecifiques')
    body.append('ec', event.params.ec)

    await fetch(this.urlUpdateValue, {
      method: 'POST',
      body,
    }).then(() => {
      const quitus = document.getElementById('ec_step4_quitus')
      const choixMccc = document.getElementById('choix_type_mccc')
      this.dispatch('update-form', { detail: { state: event.target.checked } })
      if (event.target.checked) {
        quitus.removeAttribute('disabled')
        choixMccc.removeAttribute('disabled')
      } else {
        quitus.setAttribute('disabled', 'disabled')
        choixMccc.setAttribute('disabled', 'disabled')
      }
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
