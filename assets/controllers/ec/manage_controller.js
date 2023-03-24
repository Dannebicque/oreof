/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/manage_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/03/2023 12:37
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../../js/callOut'

export default class extends Controller {
  static targets = ['matieres']

  code = 0

  matieres = []

  static values = {
    url: String,
    ue: Number,
    parcours: Number,
  }

  connect() {
  }

  async changeNatureEc(event) {
    const response = await fetch(`${this.urlValue}?choix=${event.target.value}`)
    this.matieresTarget.innerHTML = await response.text()
  }

  addFromListe(event) {
    // ne pas soumettre le formulaire
    event.preventDefault()

    // récupérer le choix dans la liste déroulante #ficheMatiere et l'ajouter dans le tableau HTML
    const select = document.getElementById('ficheMatiere')
    const option = select.options[select.selectedIndex]
    this.matieres.push(`id_${option.value}`)

    const matiereLibelle = option.text
    const table = document.getElementById('tableFiches')
    const row = table.insertRow(-1)
    const cell1 = row.insertCell(0)
    const cell2 = row.insertCell(1)
    const cell3 = row.insertCell(2)
    cell1.innerHTML = String.fromCharCode((this.code + 1) + 64)
    cell2.innerHTML = matiereLibelle
    cell3.innerHTML = '<button class="text-danger"><i class="fas fa-trash"></i></button>'
    this.code++
    console.log(this.matieres)
  }

  addNewFiche(event) {
    event.preventDefault()
    const matiereLibelle = document.getElementById('ficheMatiereLibelle').value
    const table = document.getElementById('tableFiches')
    const row = table.insertRow(-1)
    const cell1 = row.insertCell(0)
    const cell2 = row.insertCell(1)
    const cell3 = row.insertCell(2)
    this.matieres.push(`ac_${matiereLibelle}`)

    cell1.innerHTML = String.fromCharCode((this.code + 1) + 64)
    cell2.innerHTML = `${matiereLibelle} <i>(a créer)</i>`
    cell3.innerHTML = '<button class="text-danger"><i class="fas fa-trash"></i></button>'
    this.code++
    console.log(this.matieres)
  }

  valider(event) {
    event.preventDefault()

    // ajouter le tableau matieres aux données du formulaire

    const form = document.getElementById('formEc')
    const input = document.createElement('input')
    input.type = 'hidden'
    input.name = 'matieres'
    input.value = this.matieres.join(',')
    form.appendChild(input)

    fetch(form.action, {
      method: form.method,
      body: new URLSearchParams(new FormData(form)),
    })
      .then((response) => response.json())
      .then(async () => {
        callOut('Sauvegarde effectuée', 'success')
        this.dispatch('modalHide', { detail: { ue: this.ueValue.id, parcours: this.parcoursValue.id } })
        // todo: le dispatch ne remonte pas jusque la structure ??? problème de filtre sur ue/parcours ?
      })
  }
}
