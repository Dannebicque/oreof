/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/mutualise_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 08/03/2023 09:25
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../../js/callOut'

export default class extends Controller {
  static values = {
    url: String,
  }

  static targets = ['liste']

  connect() {
    this._updateListe()
  }

  async _updateListe() {
    this.listeTarget.innerHTML = ''
    const response = await fetch(this.urlValue, {
      method: 'POST',
      body: JSON.stringify({
        field: 'liste',
      }),
    })
    this.listeTarget.innerHTML = await response.text()
  }

  changeComposante(event) {
    // effacer toutes les options du select avec un id parcours
    const parcours = document.getElementById('parcours')
    const formation = document.getElementById('formation')

    while (formation.options.length > 0) {
      formation.remove(0);
    }

    while (parcours.options.length > 0) {
      parcours.remove(0);
    }

    this._getData(event.target.value, 'formation')
  }

  async ajouter() {
    const formation = document.getElementById('formation').value
    const parcours = document.getElementById('parcours').value
    await fetch(this.urlValue, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        formation,
        parcours,
        field: 'save',
      }),
    }).then(() => {
      callOut('Ajout effectué', 'success')
      this._updateListe()
    })
  }

  async delete(event) {
    event.preventDefault()
    if (confirm('Voulez-vous vraiment supprimer cette mutualisation ?')) {
      const body = {
        method: 'POST',
        body: JSON.stringify({
          field: 'delete',
          sem: event.params.sem,
        }),
      }
      await fetch(this.urlValue, body).then(() => {
        callOut('Suppression effectuée', 'success')
        this._updateListe()
      })
    }
  }

  changeFormation(event) {
    const parcours = document.getElementById('parcours')

    while (parcours.options.length > 0) {
      parcours.remove(0);
    }

    this._getData(event.target.value, 'parcours')
  }

  async _getData(value, field) {
    const response = await fetch(this.urlValue, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        field,
        value,
      }),
    })
    const data = await response.json()
    this._updateSelect(data, field)
  }

  _updateSelect(data, field) {
    const select = document.getElementById(`${field}`)

    if (data.length === 0) {
      const option = document.createElement('option')
      option.value = null
      option.text = 'Aucune donnée'
      select.add(option, null)
    }

    if (data.length > 1) {
      const option = document.createElement('option')
      option.value = null
      option.text = 'Choisir dans la liste'
      select.add(option, null)
    }
    data.forEach((item) => {
      const option = document.createElement('option')
      option.value = item.id
      option.text = item.libelle
      select.add(option, null)
    })

    if (data.length === 1) {
      // une seule valeur, on actualise la suivante
      if (field === 'formation') {
        this._getData(data[0].id, 'parcours')
      }
    }
  }
}
