/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ue_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/03/2023 11:22
 */

import { Controller } from '@hotwired/stimulus'
import { useDebounce } from 'stimulus-use'

export default class extends Controller {
  static debounces = ['changeNatureUeEcTexte', 'changeTypeUeTexte'];

  connect() {
    useDebounce(this)
  }

  ajoutTypeUe(event) {
    event.preventDefault()
    document.getElementById('typeUeTexte').classList.remove('d-none')
  }

  changeTypeUeTexte(event) {
    event.preventDefault()
    document.getElementById('ue_typeUe').disabled = event.currentTarget.value.length > 0
  }
}
