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
    typeMccc: String,
  }

  connect() {
    if (this.typeMcccValue !== null) {
      this._loadTypeMccc(this.typeMcccValue)
    }
  }

  changeType(event) {
    if (confirm('Attention, vous allez perdre les données saisies. Êtes-vous sûr ?')) {
      this._loadTypeMccc(event.target.value)
    }
  }

  async _loadTypeMccc(typeMccc) {
    const params = new URLSearchParams()
    params.append('type', typeMccc)

    const response = await fetch(`${this.urlValue}?${params.toString()}`)
    this.zoneTarget.innerHTML = await response.text()
    console.log(typeMccc)
    if (typeMccc === 'cc') {
      this.saveDataCc()
    } else if (typeMccc === 'ct') {
      this.saveDataCt()
    } else if (typeMccc === 'cc_ct') {
      this.saveDataCcCt()
    }
  }

  saveDataCcCt() {
    // on vérifie que le pourcentage est bien de 100
    const total = parseFloat(document.getElementById('pourcentage_s1_cc').value) + parseFloat(document.getElementById('pourcentage_s1_et').value)

    const option1 = document.querySelector('#typeEpreuve_s1_et option:checked')
    document.getElementById('duree_s1_et').disabled = !(parseInt(option1.dataset.hasduree, 10) === 1)

    if (document.getElementById('duree_s1_et').disabled === true) {
      document.getElementById('duree_s1_et').value = ''
    }

    const option2 = document.querySelector('#typeEpreuve_s2_et option:checked')
    document.getElementById('duree_s2_et').disabled = !(parseInt(option2.dataset.hasduree, 10) === 1)

    if (document.getElementById('duree_s2_et').disabled === true) {
      document.getElementById('duree_s2_et').value = ''
    }

    if (total !== 100) {
      this.zoneErreurTarget.classList.remove('d-none')
      this.zoneErreurTarget.innerHTML = 'Le pourcentage doit être de 100%'
    } else {
      this.zoneErreurTarget.classList.add('d-none')
      this.zoneErreurTarget.innerHTML = ''
    }
  }

  saveDataCt() {
    const option1 = document.querySelector('#typeEpreuve_s1_et option:checked')
    document.getElementById('duree_s1_et').disabled = !(parseInt(option1.dataset.hasduree, 10) === 1)

    if (document.getElementById('duree_s1_et').disabled === true) {
      document.getElementById('duree_s1_et').value = ''
    }

    const option2 = document.querySelector('#typeEpreuve_s2_et option:checked')
    document.getElementById('duree_s2_et').disabled = !(parseInt(option2.dataset.hasduree, 10) === 1)

    if (document.getElementById('duree_s2_et').disabled === true) {
      document.getElementById('duree_s2_et').value = ''
    }
  }

  saveDataCc() {
    const option = document.querySelector('#typeEpreuve_s2_et option:checked')
    document.getElementById('duree_s2_et').disabled = !(parseInt(option.dataset.hasduree, 10) === 1)

    if (document.getElementById('duree_s2_et').disabled === true) {
      document.getElementById('duree_s2_et').value = ''
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
                       name="pourcentage[${numEp}]"
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
        </div>
`
    document.getElementById('epreuve_cci').appendChild(div)
  }

  removeEpreuveCci(event) {
    event.preventDefault()
    const div = event.target.closest('.epreuve')
    div.remove()

    // renuméroter les épreuves
    let numEp = 1
    document.querySelectorAll('.epreuve').forEach((element) => {
      element.querySelector('strong').innerHTML = `Epreuve N°${numEp}`
      element.querySelector('input').setAttribute('id', `pourcentage_s${numEp}_cc`)
      element.querySelector('input').setAttribute('name', `pourcentage[${numEp}]`)
      numEp++
    })
  }
}
