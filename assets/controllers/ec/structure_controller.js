import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { calculEtatStep } from '../../js/calculEtatStep'

export default class extends Controller {
  static values = {
    urlSave: String,
  }

  saveModaliteEnseignement(event) {
    const valeur = event.target.value
    this._save({
      action: 'modalitesEnseignement',
      value: valeur,
    })

    const blocPresentiel = document.getElementById('bloc_presentiel')
    const blocDistanciel = document.getElementById('bloc_distanciel')

    if (valeur === '0') {
      // présentiel
      blocPresentiel.style.display = 'block'
      blocDistanciel.style.display = 'none'
    }

    if (valeur === '1') {
      // hybride
      blocPresentiel.style.display = 'block'
      blocDistanciel.style.display = 'block'
    }

    if (valeur === '2') {
      // distanciel
      blocPresentiel.style.display = 'none'
      blocDistanciel.style.display = 'block'
    }
  }

  saveEcts(event) {
    this._save({
      field: 'ects',
      action: 'float',
      value: event.target.value,
    })
  }

  saveVolume(event) {
    this._save({
      field: event.params.type,
      action: 'float',
      value: event.target.value,
    })
  }

  async _save(options) {
    await saveData(this.urlSaveValue, options).then(async () => {
      await updateEtatOnglet(this.urlSaveValue, 'onglet4', 'ec')
    })
  }

  async etatStep(event) {
    calculEtatStep(this.urlSaveValue, 4, event, 'ec')
    // this._save({
    //   action: 'etatStep',
    //   value: 4,
    //   isChecked: event.target.checked,
    // })
    //
    // const parent = event.target.closest('.alert')
    // if (event.target.checked) {
    //   parent.classList.remove('alert-warning')
    //   parent.classList.add('alert-success')
    // } else {
    //   parent.classList.remove('alert-success')
    //   parent.classList.add('alert-warning')
    // }
    //
    // await updateEtatOnglet(this.urlSaveValue, 'onglet4', 'ec')
  }
}
