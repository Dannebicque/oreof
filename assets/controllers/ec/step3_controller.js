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
  }

  connect() {
    document.getElementById('ec_step3_objectifs').addEventListener('trix-blur', this.saveObjectifs.bind(this))
  }

  saveObjectifs() {
    this._save({
      field: 'objectifs',
      action: 'textarea',
      value: trixEditor('ec_step3_objectifs'),
    })
  }

  changeBcc(event) {
    if (event.target.checked) {
      document.getElementById(`bcc_${event.params.id}_competence`).classList.remove('d-none')
    } else {
      document.getElementById(`bcc_${event.params.id}_competence`).classList.add('d-none')
      document.querySelectorAll(`.bcc_${event.params.id}`).forEach((element) => {
        element.checked = false
      })
      this._save({ action: 'removeBcc', value: event.params.id })
    }
  }

  changeCompetence(event) {
    this._save({ action: 'addCompetence', value: event.params.id, checked: event.target.checked })
  }

  etatStep(event) {
    calculEtatStep(this.urlValue, 3, event, 'ec')
    // this._save({
    //   action: 'etatStep',
    //   value: 3,
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
      await updateEtatOnglet(this.urlValue, 'onglet3', 'ec')
    })
  }
}
