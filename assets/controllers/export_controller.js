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
  }

  valideExport(event) {
    event.preventDefault()
    // récupère les données du formulaire + les données de la liste

    let isValid = true
    // vérifier les champs obligatoires et mettre en surbrillance les champs non remplis
    if (document.getElementById('annee_universitaire').value === '') {
      document.getElementById('annee_universitaire').classList.add('is-invalid')
      isValid = false
    } else {
      document.getElementById('annee_universitaire').classList.remove('is-invalid')
    }

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
    }

    if (isValid) {
      const liste = document.querySelectorAll('.check-all:checked')
      const data = new FormData()

      // ajoute les données de la liste au formulaire
      liste.forEach((element) => {
        data.append('liste[]', element.value)
      })

      // ajoute les données du formulaire au formulaire
      data.append('annee_universitaire', document.getElementById('annee_universitaire').value)
      data.append('composante', document.getElementById('composante').value)
      data.append('type_document', document.getElementById('type_document').value)
      data.append('date', document.getElementById('date').value)

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
    const annee = document.getElementById('annee_universitaire').value
    const composante = document.getElementById('composante').value

    if (annee !== '' || composante !== '') {
      const body = new URLSearchParams({
        annee,
        composante,
      })

      const response = await fetch(`${this.urlValue}?${body.toString()}`)
      this.listeTarget.innerHTML = await response.text()
    }
  }
}
