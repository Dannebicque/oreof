import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import callOut from '../../js/callOut'
import { calculEtatStep } from '../../js/calculEtatStep'

export default class extends Controller {
  static targets = ['detail']

  static values = {
    url: String,
    urlSave: String,
    urlGenereStructure: String,
  }

  connect() {
    this._loadParcours()
  }

  recopieStructure() {
    const elt = document.getElementById('parcoursSource')
    const nameParcours = elt.options[elt.selectedIndex].text
    if (confirm(`Voulez-vous vraiment recopier la structure du parcours "${nameParcours}" ? Cela effacera les données présentes. `)) {
      this._structure({
        action: 'recopieStructure',
        value: document.getElementById('parcoursSource').value,
      })
      callOut('Recopie effectuée.', 'success')
    }
  }

  reinitialiseStructure() {
    if (confirm('Voulez-vous vraiment réinitialiser le semestre ? Cela effacera les données présentes. ')) {
      this._structure({
        action: 'reinitialiseStructure',
      })
      callOut('Recopie effectuée.', 'success')
    }
  }

  genereStructure() {
    if (confirm('Voulez-vous vraiment recopier générer la structure du semestre ? ')) {
      this._structure({
        action: 'genereStructure',
      })
      callOut('Recopie effectuée.', 'success')
    }
  }

  async _loadParcours() {
    const response = await fetch(this.urlValue)
    this.detailTarget.innerHTML = await response.text()
  }

  etatStep(event) {
    calculEtatStep(this.urlSaveValue, 4, event, 'parcours')
  }

  async _structure(options) {
    await saveData(this.urlGenereStructureValue, options).then(async () => {
      this._loadParcours()
    })
  }

  async _save(options) {
    await saveData(this.urlSaveValue, options).then(async () => {
      await updateEtatOnglet(this.urlSaveValue, 'onglet4', 'parcours')
    })
  }
}
