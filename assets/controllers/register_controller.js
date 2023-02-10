import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    urlFormation: String,
    urlComposante: String,
  }

  changeCentre(event) {
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
    await fetch(url).then((response) => response.json()).then(
      (data) => {
        const items = data
        const selectMention = document.getElementById('selectListe')
        selectMention.innerHTML = ''

        let option = document.createElement('option')
        option.value = null
        option.text = 'Choisir dans la liste'
        option.selected = true
        selectMention.appendChild(option)

        items.forEach((mention) => {
          option = document.createElement('option')
          option.value = mention.id
          option.text = mention.libelle
          selectMention.appendChild(option)
        })
      },
    )
  }
}
