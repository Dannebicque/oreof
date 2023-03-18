/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/liste_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 12/03/2023 16:08
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../../js/callOut'

export default class extends Controller {
  async deplacerEc(event) {
    console.log(event.params)
    event.preventDefault()
    const { url } = event.params
    await fetch(url).then(() => {
      callOut('EC déplacé', 'success')
      this.dispatch('refreshListeEc', { detail: { ue: event.params.ue, parcours: event.params.parcours } })
    })
  }
}
