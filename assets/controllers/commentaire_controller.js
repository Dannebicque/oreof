/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/base_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 14:53
 */

import { Controller } from '@hotwired/stimulus'
import JsonResponse from '../js/JsonResponse'

export default class extends Controller {
  static targets = ['liste']

  static values = {
    urlListe: String,
  }

  connect() {
    this._loadListe()
  }

  async sauvegarde(event) {
    // récupérer le texte et l'envoyer à l'action du formulaire
    event.preventDefault()
    const form = document.getElementById('formCommentaire')

    await fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
    }).then((response) => {
      JsonResponse(response)
      this._loadListe()
    })
  }

  async _loadListe() {
    this.listeTarget.innerHTML = window.da.loaderStimulus
    await fetch(this.urlListeValue)
      .then((response) => response.text())
      .then((html) => {
        this.listeTarget.innerHTML = html
      })
  }

  async delete(event) {
    event.preventDefault()
    if (confirm('Voulez-vous vraiment supprimer ce commentaire ?') === true) {
      console.log(event.target.href)
      await fetch(event.target.href, {
        method: 'DELETE',
      }).then((response) => {
        JsonResponse(response)
        this._loadListe()
      })
    }
  }
}
