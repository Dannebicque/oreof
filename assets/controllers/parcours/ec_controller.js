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
    urlUpdate: String,
  }

  async changeTypeEc(event) {
    const body = new FormData()
    body.append('value', event.target.value)
    body.append('field', 'typeEc')
    body.append('ec', event.params.ec)

    await fetch(this.urlUpdateValue, {
      method: 'POST',
      body,
    }).then((response) => {
      // response.json()
      JsonResponse(response)
    })
  }
}
