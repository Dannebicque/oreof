import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {
  static targets = [
    'liste',
  ]
  static values = {
    url: String,
    urlListeParcours: String,
    urlGenereStructre: String,
    hasParcours: Boolean,
  }

  connect() {
    if (this.hasParcoursValue === true) {
      this._refreshListe()
    }
  }

  async _refreshListe() {
    this.listeTarget.innerHTML = window.da.loaderStimulus
    const response = await fetch(this.urlListeParcoursValue)
    this.listeTarget.innerHTML = await response.text()
  }

  refreshListe() {
    this._refreshListe()
  }

  changeSemestre(event) {
    saveData(this.urlValue,{
      action: 'structureSemestres',
      value: event.target.value,
      semestre: event.params.semestre,
    })
  }

  changeHasParcours(event) {
    const data = event.target.value

    if (data == 1) {
      document.getElementById('liste_Parcours').classList.remove('d-none');
      document.getElementById('bloc_semestre').classList.remove('d-none');
    } else {
      document.getElementById('liste_Parcours').classList.add('d-none');
      document.getElementById('bloc_semestre').classList.add('d-none');
    }

    saveData(this.urlValue,{
      field: 'hasParcours',
      action: 'yesNo',
      value: event.target.value,
    })

    this._refreshListe()
  }

  initStructure() {
    if (confirm('Voulez-vous vraiment initialiser la structure ?')) {
      window.location = this.urlGenereStructreValue
    }
  }
}
