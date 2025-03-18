/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/mutualise_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 09:25
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../js/callOut'
import JsonResponse from '../js/JsonResponse'

export default class extends Controller {
  static values = {
    url: String,
    urlValide: String,
  }

  static targets = ['liste']

  connect() {
    this._updateListe()
  }

  valideExport(event) {
    event.preventDefault()
    // récupère les données du formulaire + les données de la liste
    let liste = []
    let typeDocs = null
    let isValid = true

    if (!document.getElementById('type_document_global') || document.getElementById('type_document_global').value === '') {
      if (document.getElementById('composante').value === '') {
        document.getElementById('composante').classList.add('is-invalid')
        isValid = false
      } else {
        document.getElementById('composante').classList.remove('is-invalid')
      }

      if (document.getElementById('type_document').value === '') {
        document.getElementById('type_document').classList.add('is-invalid')
        isValid = false
      } else {
        document.getElementById('type_document').classList.remove('is-invalid')
        typeDocs = document.getElementById('type_document').value
      }

      liste = document.querySelectorAll('.check-all:checked')
      if (liste.length === 0) {
        isValid = false
        callOut('Veuillez sélectionner au moins une formation', 'danger')
      }
    } else {
      typeDocs = document.getElementById('type_document_global').value
    }

    if (isValid) {
      const data = new FormData()

      // ajoute les données de la liste au formulaire
      liste.forEach((element) => {
        data.append('liste[]', element.value)
      })

      // ajoute les données du formulaire au formulaire
      data.append('composante', document.getElementById('composante').value)
      data.append('type_document', typeDocs)
      // data.append('type_document_global', document.getElementById('type_document_global').value)
      data.append('date', document.getElementById('date') ? document.getElementById('date').value : '')

      // envoie le formulaire
      fetch(this.urlValideValue, {
        method: 'POST',
        body: data,
      }).then((response) => {
        JsonResponse(response)
      })
    } else {
      callOut('Veuillez remplir les champs obligatoires', 'danger')
    }
  }

  async changeListe() {
    this._updateListe()
  }

  async _updateListe() {
    const composante = document.getElementById('composante').value
    if (composante !== '') {
      const body = new URLSearchParams({
        composante,
      })

      const response = await fetch(`${this.urlValue}?${body.toString()}`)
      this.listeTarget.innerHTML = await response.text()
    }
  }
}
