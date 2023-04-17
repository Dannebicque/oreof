/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/base_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 14:53
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['zone', 'zoneErreur']

  static values = {
    url: String,
  }

  async changeType(event) {
    const params = new URLSearchParams()
    params.append('type', event.target.value)

    const response = await fetch(`${this.urlValue}?${params.toString()}`)
    this.zoneTarget.innerHTML = await response.text()
  }

  saveDataCcCt() {
    // on vérifie que le pourcentage est bien de 100
    const total = parseFloat(document.getElementById('pourcentage_s1_cc').value) + parseFloat(document.getElementById('pourcentage_s1_et').value)

    if (total !== 100) {
      this.zoneErreurTarget.classList.remove('d-none')
      this.zoneErreurTarget.innerHTML = 'Le pourcentage doit être de 100%'
    } else {
      this.zoneErreurTarget.classList.add('d-none')
      this.zoneErreurTarget.innerHTML = ''
    }
  }

  saveDataCci() {
    let total = 0
    const erreurs = []
    document.querySelectorAll('.pourcentage').forEach((element) => {
      total += parseFloat(element.value)
      if (element.value >= 50) {
        erreurs.push('Le pourcentage d\'une épreuve ne doit pas dépasser 50%')
      }
    })

    if (total !== 100) {
      erreurs.push('Le pourcentage doit être de 100%')
    }

    if (erreurs.length > 0) {
      this.zoneErreurTarget.classList.remove('d-none')
      this.zoneErreurTarget.innerHTML = erreurs.join('<br>')
    } else {
      this.zoneErreurTarget.classList.add('d-none')
      this.zoneErreurTarget.innerHTML = ''
    }
  }

  addEpreuveCci(event) {
    event.preventDefault()
    // ajouter un nouveau champs pour une nouvelle épreuve
    const div = document.createElement('div')
    const nbEpreuves = document.querySelectorAll('.epreuve').length
    const numEp = nbEpreuves + 1
    div.classList.add('row')
    div.classList.add('epreuve')
    div.innerHTML = ` <div class="col-4">

            <strong>Epreuve N°${numEp}</strong>
        </div>
        <div class="col-6">
            <label for="pourcentage_s${numEp}_cc">
                Pourcentage
                <i class="fal fa-question-circle ms-1"
                   data-controller="tooltip"
                   data-tooltip-placement-value="bottom"
                   aria-label="{{ 'mccc_licence.helps.cc.pourcentage_s1_et.help'|trans({}, 'help') }}"
                   data-bs-original-title="{{ 'mccc_licence.helps.cc.pourcentage_s1_et.help'|trans({}, 'help') }}"></i>
            </label>
            <div class="input-group">
                <input type="text" class="form-control pourcentage"
                       id="pourcentage_s${numEp}_cc"
                       name="pourcentage_s${numEp}_cc"
                       data-action="change@mccc--licence#saveDataCci"
                       value=""
                >
                <span class="input-group-text">%</span>
            </div>
        </div>
        <div class="col-2">
        &nbsp;<br>
        <button type="button" class="btn btn-danger btn-sm" data-action="click->mccc--licence#removeEpreuveCci">
            <i class="fas fa-trash"></i>
        </button>
`
    document.getElementById('epreuve_cci').appendChild(div)
  }

  removeEpreuveCci(event) {
    event.preventDefault()
    const div = event.target.closest('.epreuve')
    div.remove()
  }
}
