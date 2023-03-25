/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/step2_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/03/2023 21:14
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
  }

  connect() {
    document.getElementById('fiche_matiere_step2_description').addEventListener('trix-blur', this.saveDescription.bind(this))
  }

  saveDescription() {
    this._save({
      field: 'description',
      action: 'textarea',
      value: trixEditor('fiche_matiere_step2_description'),
    })
  }

  changeLangue(event) {
    this._save({
      field: event.params.type,
      action: 'langue',
      value: event.target.value,
      isChecked: event.target.checked,
    })
  }

  changeNatureUeEc(event) {
    this._save({
      action: 'natureUeEc',
      value: event.target.value,
    })
  }

  etatStep(event) {
    calculEtatStep(this.urlValue, 2, event, 'ec')
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet2', 'ec')
    })
  }
}
