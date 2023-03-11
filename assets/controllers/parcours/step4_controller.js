import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import callOut from '../../js/callOut'

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
    this._save({
      action: 'etatStep',
      value: 4,
      isChecked: event.target.checked,
    })

    const parent = event.target.closest('.alert')
    if (event.target.checked) {
      parent.classList.remove('alert-warning')
      parent.classList.add('alert-success')
    } else {
      parent.classList.remove('alert-success')
      parent.classList.add('alert-warning')
    }
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
