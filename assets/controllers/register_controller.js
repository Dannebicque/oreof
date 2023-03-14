import { Controller } from '@hotwired/stimulus'
import TomSelect from 'tom-select'

export default class extends Controller {
  static values = {
    urlComposante: String,
    urlFormation: String,
  }

  selectMention = null;

  connect() {
    this.selectMention = document.getElementById('selectListe')
    const tom = new TomSelect(this.selectMention)
  }

  changeCentre(event) {
    const val = event.target.value
    if (val === 'cg_etablissement' || parseInt(val, 10) === 1) {
      const tom = this.selectMention.tomselect
      tom.clear()
      tom.disable()
      document.getElementById('selectListe').classList.add('d-none')
    } else if (val === 'cg_composante' || parseInt(val, 10) === 0) {
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
        const tom = this.selectMention.tomselect
        const tab = []

        items.forEach((mention) => {
          tab.push({ value: mention.id, text: mention.libelle })
        })

        tom.clear()
        tom.clearOptions()
        tom.enable()

        tom.addOptions(tab)
        tom.settings.placeholder = 'Choisir dans la liste'
        tom.inputState();
      },
    )
  }
}
