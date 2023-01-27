import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'
import { addCallout } from '../js/callOut'

export default class extends Controller {

  static values = {
    urlFormation: String,
    urlComposante: String,
  }

  async changeCentre(event) {
    const val = event.target.value

    if (val === 'cg_etablissement') {
      document.getElementById('selectListe').classList.add('d-none')
    } else if (val === 'cg_composante') {
      this._updateSelect(this.urlComposanteValue)
      document.getElementById('selectListe').classList.remove('d-none')

    } else if (val === 'cg_formation') {
      this._updateSelect(this.urlFormationValue)
      document.getElementById('selectListe').classList.remove('d-none')

    }
  }

  async _updateSelect(url) {
    await fetch(url).then(response => response.json()).then(
      data => {
        const mentions = data
        let selectMention = document.getElementById('selectListe')
        selectMention.innerHTML = ''

        mentions.forEach(mention => {
          let option = document.createElement('option')
          option.value = mention.id
          option.text = mention.libelle
          selectMention.appendChild(option)
        })
      },
    )
  }
}
