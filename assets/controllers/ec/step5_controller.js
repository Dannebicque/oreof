/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/step5_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 13/03/2023 21:49
 */

import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { calculEtatStep } from '../../js/calculEtatStep'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
  }

  saveData(event) {
    this._save({
      field: event.params.field,
      action: 'mcccs',
      type: event.params.action,
      value: event.target.value,
    })
  }

  etatStep(event) {
    calculEtatStep(this.urlValue, 5, event, 'ec')

    // this._save({
    //   action: 'etatStep',
    //   value: 5,
    //   isChecked: event.target.checked,
    // })
    //
    // const parent = event.target.closest('.alert')
    // if (event.target.checked) {
    //   parent.classList.remove('alert-warning')
    //   parent.classList.add('alert-success')
    // } else {
    //   parent.classList.remove('alert-success')
    //   parent.classList.add('alert-warning')
    // }
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet5', 'ec')
    })
  }
}
