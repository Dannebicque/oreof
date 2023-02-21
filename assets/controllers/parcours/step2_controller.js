import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
  }

  changeModaliteEnseignement(event) {
    this._save({
      field: 'modalitesEnseignement',
      action: 'modalitesEnseignement',
      value: event.target.value,
    })
  }

  changeStage(event) {
    this._save({
      field: 'hasStage',
      action: 'yesNo',
      value: event.target.value,
    })
    if (event.target.value == 1) {
      document.getElementById('blocStage').style.display = 'block';
    } else {
      document.getElementById('blocStage').style.display = 'none';
    }
  }

  saveStageText() {
    this._save({
      field: 'stageText',
      action: 'textarea',
      value: document.getElementById('parcours_step2_stageText').value,
    })
  }

  changeNbHeuresStages(event) {
    this._save({
      field: 'nbHeuresStages',
      action: 'float',
      value: event.target.value,
    })
  }

  /// // Projet /////
  changeProjet(event) {
    this._save({
      field: 'hasProjet',
      action: 'yesNo',
      value: event.target.value,
    })
    if (event.target.value == 1) {
      document.getElementById('blocProjet').style.display = 'block';
    } else {
      document.getElementById('blocProjet').style.display = 'none';
    }
  }

  saveProjetText() {
    this._save({
      field: 'projetText',
      action: 'textarea',
      value: document.getElementById('parcours_step2_projetText').value,
    })
  }

  changeNbHeuresProjet(event) {
    this._save({
      field: 'nbHeuresProjet',
      action: 'float',
      value: event.target.value,
    })
  }

  changeNbHeuresSituationPro(event) {
    this._save({
      field: 'nbHeuresSituationPro',
      action: 'float',
      value: event.target.value,
    })
  }

  /// // Mémoire /////
  changeMemoire(event) {
    this._save({
      field: 'hasMemoire',
      action: 'yesNo',
      value: event.target.value,
    })
    if (event.target.value == 1) {
      document.getElementById('blocMemoire').style.display = 'block';
    } else {
      document.getElementById('blocMemoire').style.display = 'none';
    }
  }

  saveMemoireText() {
    this._save({
      field: 'memoireText',
      action: 'textarea',
      value: document.getElementById('parcours_step2_memoireText').value,
    })
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet2', 'parcours')
    })
  }
}
