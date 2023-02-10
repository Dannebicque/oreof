import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
  }

  changeModaliteEnseignement(event) {
    console.log(event.target.value)
    saveData(
      this.urlValue,
      {
        field: 'modalitesEnseignement',
        action: 'modalitesEnseignement',
        value: event.target.value,
      },
    )
  }

  changeStage(event) {
    saveData(
      this.urlValue,
      {
        field: 'hasStage',
        action: 'yesNo',
        value: event.target.value,
      },
    )
    if (event.target.value == 1) {
      document.getElementById('blocStage').style.display = 'block';
    } else {
      document.getElementById('blocStage').style.display = 'none';
    }
  }

  saveStageText() {
    saveData(this.urlValue, {
      field: 'stageText',
      action: 'textarea',
      value: document.getElementById('parcours_step2_stageText').value,
    })
  }

  changeNbHeuresStages(event) {
    saveData(
      this.urlValue,
      {
        field: 'nbHeuresStages',
        action: 'float',
        value: event.target.value,
      },
    )
  }

  /// // Projet /////
  changeProjet(event) {
    saveData(
      this.urlValue,
      {
        field: 'hasProjet',
        action: 'yesNo',
        value: event.target.value,
      },
    )
    if (event.target.value == 1) {
      document.getElementById('blocProjet').style.display = 'block';
    } else {
      document.getElementById('blocProjet').style.display = 'none';
    }
  }

  saveProjetText() {
    saveData(this.urlValue, {
      field: 'projetText',
      action: 'textarea',
      value: document.getElementById('parcours_step2_projetText').value,
    })
  }

  changeNbHeuresProjet(event) {
    saveData(
      this.urlValue,
      {
        field: 'nbHeuresProjet',
        action: 'float',
        value: event.target.value,
      },
    )
  }

  changeNbHeuresSituationPro(event) {
    saveData(
      this.urlValue,
      {
        field: 'nbHeuresSituationPro',
        action: 'float',
        value: event.target.value,
      },
    )
  }

  /// // Mémoire /////
  changeMemoire(event) {
    saveData(
      this.urlValue,
      {
        field: 'hasMemoire',
        action: 'yesNo',
        value: event.target.value,
      },
    )
    if (event.target.value == 1) {
      document.getElementById('blocMemoire').style.display = 'block';
    } else {
      document.getElementById('blocMemoire').style.display = 'none';
    }
  }

  saveMemoireText(event) {
    saveData(this.urlValue, {
      field: 'memoireText',
      action: 'textarea',
      value: document.getElementById('parcours_step2_memoireText').value,
    })
  }
}
