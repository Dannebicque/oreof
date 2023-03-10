import { Controller } from '@hotwired/stimulus'
import callOut from '../../js/callOut'

export default class extends Controller {
  static values = {
    url: String,
  }

  changeComposante(event) {
    this._getData(event.target.value, 'formation')
  }

  async ajouter() {
    console.log('ajouter')
    const ue = document.getElementById('ue').value
    const response = await fetch(this.urlValue, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        value: ue,
        field: 'save',
      }),
    }).then(() => {
      callOut('Ajout effectué', 'success')
      this.dispatch('refreshListe')
    })
  }

  changeFormation(event) {
    this._getData(event.target.value, 'parcours')
  }

  changeParcours(event) {
    this._getData(event.target.value, 'semestre')
  }

  changeSemestre(event) {
    this._getData(event.target.value, 'ue')
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
      option.value = ''
      option.text = 'Aucune donnée'
      select.add(option)
    }

    if (data.length > 1) {
      const option = document.createElement('option')
      option.value = ''
      option.text = 'Choisir dans la liste'
      select.add(option)
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
      if (field === 'parcours') {
        this._getData(data[0].id, 'semestre')
      }
      if (field === 'semestre') {
        this._getData(data[0].id, 'ue')
      }
    }
  }
}
