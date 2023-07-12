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
          ue: event.params.ue,
        }),
      }
      await fetch(this.urlValue, body).then(() => {
        callOut('Suppression effectuée', 'success')
        this._updateListe()
      })
    }
  }

  changeFormation(event) {
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
    select.innerHTML = ''
    if (data.length === 0) {
      const option = document.createElement('option')
      option.value = null
      option.text = 'Aucune donnée'
      select.add(option)
    }

    if (data.length > 1) {
      const option = document.createElement('option')
      option.value = null
      option.text = 'Choisir dans la liste'
      option.selected = true
      select.add(option)

      if (field === 'parcours') {
        const optionAll = document.createElement('option')
        optionAll.value = 'all'
        optionAll.text = 'Mutualiser avec tous les parcours de la mention'
        select.add(optionAll, null)
      }
    }
    data.forEach((item) => {
      const option = document.createElement('option')
      option.value = item.id
      option.text = item.libelle
      select.add(option)
    })

    if (data.length === 1) {
      // une seule valeur, on actualise la suivante
      if (field === 'formation') {
        this._getData(data[0].id, 'parcours')
      }
    }
  }
}
