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

  ajoutTypeEc(event) {
    event.preventDefault()
    document.getElementById('typeEcTexte').classList.remove('d-none')
  }

  changeTypeEcTexte(event) {
    event.preventDefault()
    document.getElementById('element_constitutif_typeEc').disabled = event.currentTarget.value.length > 0
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
    let tbody = table.getElementsByTagName('tbody')[0]; // sélectionne le tbody (ou crée-le s'il n'existe pas)
    if (!tbody) {
      tbody = document.createElement('tbody'); // crée le tbody s'il n'existe pas
      table.appendChild(tbody); // ajoute le tbody à la table
    }
    const row = tbody.insertRow()
    const cell2 = row.insertCell(0)
    const cell3 = row.insertCell(1)
    cell2.innerHTML = matiereLibelle
    cell3.innerHTML = '<button class="btn text-danger"><i class="fas fa-trash"></i> Supprimer</button>'

    // ajouter un écouteur sur le bouton supprimer
    cell3.addEventListener('click', (ev) => {
      ev.preventDefault()
      const index = this.matieres.indexOf(`id_${option.value}`)
      if (index > -1) {
        this.matieres.splice(index, 1)
      }
      table.deleteRow(row.rowIndex)
    })

    this.code++
  }

  addNewFiche(event) {
    event.preventDefault()
    const matiereLibelle = document.getElementById('ficheMatiereLibelle').value
    const table = document.getElementById('tableFiches')
    let tbody = table.getElementsByTagName('tbody')[0]; // sélectionne le tbody (ou crée-le s'il n'existe pas)
    if (!tbody) {
      tbody = document.createElement('tbody'); // crée le tbody s'il n'existe pas
      table.appendChild(tbody); // ajoute le tbody à la table
    }
    const row = tbody.insertRow()
    const cell2 = row.insertCell(0)
    const cell3 = row.insertCell(1)

    this.matieres.push(`ac_${matiereLibelle}`)

    cell2.innerHTML = `${matiereLibelle} <i>(a créer)</i>`
    cell3.innerHTML = '<button class="btn text-danger"><i class="fas fa-trash"></i> Supprimer</button>'

    // ajouter un écouteur sur le bouton supprimer
    cell3.addEventListener('click', (ev) => {
      ev.preventDefault()
      const index = this.matieres.indexOf(`ac_${matiereLibelle}`)
      if (index > -1) {
        this.matieres.splice(index, 1)
      }
      table.deleteRow(row.rowIndex)
    })

    this.code++
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
        this.dispatch('modalHide', { detail: { ue: this.ueValue, parcours: this.parcoursValue } })
      })
  }
}
