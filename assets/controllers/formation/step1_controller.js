import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {
  static targets = [
    'content',
  ]
  static values = {
    url: String,
  }

  connect() {
    console.log('step 1')
  }

  changeSite(event) {
    this._save({
      action: 'site',
      value: event.target.value,
      isChecked: event.target.checked,
    })
  }

  changeSemestre(event) {
    this._save({
      action: 'semestre',
      value: event.target.value,
    })
  }

  changeParcours(event) {
    const data = event.target.value
    console.log(data)
    // if (data == '0') {
    //   if (confirm('Voulez-vous vraiment supprimer ce parcours ?')) {
    //     //confirm ne fonctionne qu'une seule fois??
    //     //fetch pour supprimer les parcours ? ou sur le save?
    //     this.contentTarget.innerHTML = ''
    //   } else {
    //     event.stopPropagation() //annule le changement de valeur
    //     event.target.value = '1'
    //   }
    // }

    this._save({
      field: 'hasParcours',
      action: 'yesNo',
      value: event.target.value,
    })
    //fetch pour récupérer le contenu du parcours
  }

  _save(options) {
    saveData(this.urlValue, options)
  }
}
