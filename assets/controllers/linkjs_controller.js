/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/linkjs_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/11/2023 15:57
 */

import { Controller } from '@hotwired/stimulus'
import JsonResponse from '../js/JsonResponse'

export default class extends Controller {
  openLink(event) {
    event.preventDefault()
    const url = event.target.getAttribute('href')

    fetch(url)
      .then((response) => {
        // response.json()
        JsonResponse(response)
      })
  }
}
