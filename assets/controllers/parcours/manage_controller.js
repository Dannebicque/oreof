/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/mutualise_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 09:25
 */

import { Controller } from '@hotwired/stimulus'
import JsonResponse from '../../js/JsonResponse';

export default class extends Controller {
  static values = {
    url: String,
  }

  valideDupliquer(event) {
    event.preventDefault()

    // récupérer la valeur de la radio "dupliquer"
    const dupliquer = document.querySelector('input[name="option"]:checked').value;

    if (confirm('Voulez-vous vraiment dupliquer ce parcours ?')) {
      const body = {
        method: 'POST',
        body: JSON.stringify({
          dupliquer,
        }),
      }
      fetch(this.urlValue, body).then((response) => {
        JsonResponse(response)
        this.dispatch('modalHide')
      })
    }
  }
}
