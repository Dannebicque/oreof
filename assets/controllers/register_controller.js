import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    urlComposante: String,
  }

  connect() {
    console.log('register_controller connected')
  }

  changeCentre(event) {
    console.log('changeCentre')
    const val = event.target.value
    console.log(val)
    if (val === 'cg_etablissement' || val == 1) {
      document.getElementById('selectListe').classList.add('d-none')
    } else if (val === 'cg_composante' || val == 0) {
      this._updateSelect(this.urlComposanteValue)
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
