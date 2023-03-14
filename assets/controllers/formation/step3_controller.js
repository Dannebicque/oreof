import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import callOut from '../../js/callOut'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { calculEtatStep } from '../../js/calculEtatStep'

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

  async deleteParcours(event) {
    event.preventDefault()
    if (confirm('Voulez-vous vraiment supprimer ce parcours et toutes les informations associées ?')) {
      const { id } = event.params
      const { url } = event.params
      const { csrf } = event.params
      const body = {
        method: 'DELETE',
        body: JSON.stringify({
          csrf,
        }),
      }
      await fetch(url, body).then(() => {
        callOut('Suppression effectuée', 'success')
        this._refreshListe()
        document.getElementById(`tab_parcours_${id}`).remove()
      })
    }
  }

  async _refreshListe() {
    this.listeTarget.innerHTML = window.da.loaderStimulus
    const response = await fetch(this.urlListeParcoursValue)
    this.listeTarget.innerHTML = await response.text()
  }

  refreshListe() {
    window.location = this.urlGenereStructreValue
  }

  changeSemestre(event) {
    this._save({
      action: 'structureSemestres',
      value: event.target.value,
      semestre: event.params.semestre,
    })
  }

  changeSemestreDebut(event) {
    this._save({
      field: 'semestreDebut',
      action: 'int',
      value: event.target.value,
    })
  }

  changeHasParcours(event) {
    const data = event.target.value

    if (parseInt(data, 10) === 1) {
      document.getElementById('liste_Parcours').classList.remove('d-none');
      document.getElementById('bloc_semestre').classList.remove('d-none');
    } else {
      document.getElementById('liste_Parcours').classList.add('d-none');
      document.getElementById('bloc_semestre').classList.add('d-none');
    }

    this._save({
      field: 'hasParcours',
      action: 'yesNo',
      value: event.target.value,
    })

    this._refreshListe()
  }

  initStructure() {
    if (confirm('Voulez-vous vraiment initialiser la structure ? Si des parcours ont déjà été initialisés, toutes les données associées seront supprimées.')) {
      window.location = this.urlGenereStructreValue
    }
  }

  etatStep(event) {
    calculEtatStep(this.urlValue, 3, event, 'formation')
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet3', 'formation')
    })
  }
}
