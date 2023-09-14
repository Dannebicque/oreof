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

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
    updateStep: { type: Boolean, default: true },
  }

  connect() {

  }

  saveVolume(event) {
    this._save({
      field: event.params.type,
      action: 'float',
      value: event.target.value,
    })
  }

  etatStep(event) {
    calculEtatStep(this.urlValue, 4, event, 'ec')
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      if (this.updateStepValue) {
        await updateEtatOnglet(this.urlValue, 'onglet4', 'ec')
      }
    })
  }
}
