/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/formation/step3_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/03/2023 21:28
 */

import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import callOut from '../../js/callOut'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { calculEtatStep } from '../../js/calculEtatStep'
import trixEditor from '../../js/trixEditor'

export default class extends Controller {
  static targets = [
    'liste',
  ]

  static values = {
    url: String,
    urlListeParcours: String,
    urlGenereStructure: String,
    urlReload: String,
    hasParcours: Boolean,
  }

  connect() {
    document.getElementById('form_objectifsFormation').addEventListener('trix-blur', this.saveObjectifsFormation.bind(this))
    if (this.hasParcoursValue === true) {
      this._refreshListe()
    }
  }

  saveObjectifsFormation() {
    this._save({
      field: 'objectifsFormation',
      action: 'textarea',
      value: trixEditor('form_objectifsFormation'),
    })
  }

  async dupliquerParcours(event) {
    event.preventDefault()
    if (confirm('Voulez-vous vraiment dupliquer ce parcours et toutes les informations associées ?')) {
      const { url } = event.params
      await fetch(url).then(() => {
        callOut('Duplication effectuée', 'success')
        this._refreshListe()
      })
    }
  }

  async deleteParcours(event) {
    event.preventDefault()
    if (confirm('Voulez-vous vraiment supprimer ce parcours et toutes les informations associées ?')) {
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
      })
    }
  }

  async _refreshListe() {
    this.listeTarget.innerHTML = window.da.loaderStimulus
    const response = await fetch(this.urlListeParcoursValue)
    this.listeTarget.innerHTML = await response.text()
  }

  changeSemestre(event) {
    this._save({
      action: 'structureSemestres',
      value: event.target.value,
      semestre: event.params.semestre,
    })
  }

  changeSemestreDebut(event) {
    const sem = parseInt(event.target.dataset.semestredebut, 10)

    if (sem !== event.target.value) {
      if (confirm('Voulez-vous vraiment modifier le semestre de début des parcours ? Cela  va modifier la structure de votre parcours/formation et peut effacer les semestres caduques/devenus inutiles.')) {
        if (sem > parseInt(event.target.value, 10)) {
          const liste = document.getElementById('listeSemestre')
          // on ajoute
          let html = ''
          for (let i = parseInt(event.target.value, 10); i < sem; i++) {
            html += `<li id="sem_${i}">
                    <span class="required">Semestre ${i}</span> :
                        <div class="form-check-inline">
                            <input type="radio" id="formation_parcours_${i}_tc" name="semestre_${i}" required="required"
                                class="form-check-input" value="tronc_commun"
                                 data-action="click->formation--step3#changeSemestre"
                                 data-formation--step3-semestre-param="${i}"
                                >
                                <label class="form-check-label" for="formation_parcours_${i}_tc">Tronc Commun</label>
                        </div>
                        <strong class="text-primary me-2">OU</strong>
                        <div class="form-check-inline">
                            <input type="radio" id="formation_parcours_${i}_parc" name="semestre_{{ i }}" required="required"
                                    class="form-check-input" value="parcours" 
                                    data-action="click->formation--step3#changeSemestre"
                                    data-formation--step3-semestre-param="${i}">
                           <label class="form-check-label" for="formation_parcours_${i}_parc">Parcours</label>
                        </div>
                 </li>`
          }

          // ajouter un nouvel élément au début de la liste ul/li
          liste.insertAdjacentHTML('afterbegin', html)
        } else {
          // on supprime
          for (let i = 0; i < event.target.value; i++) {
            if (document.getElementById(`sem_${i}`)) {
              document.getElementById(`sem_${i}`).remove()
            }
          }
        }

        this._save({
          action: 'semestreDebut',
          value: event.target.value,
        })
        event.target.dataset.semestredebut = event.target.value
      }
    }
  }

  changeHasParcours(event) {
    const data = event.target.value

    if (parseInt(data, 10) === 1) {
      document.getElementById('liste_Parcours').classList.remove('d-none')
      document.getElementById('bloc_semestre').classList.remove('d-none')
      document.getElementById('bloc_pas_parcours').classList.add('d-none')
    } else {
      document.getElementById('liste_Parcours').classList.add('d-none')
      document.getElementById('bloc_semestre').classList.add('d-none')
      document.getElementById('bloc_pas_parcours').classList.remove('d-none')
    }

    this._save({
      field: 'hasParcours',
      action: 'yesNo',
      value: event.target.value,
    })

    this._refreshListe()
  }

  async genereStructurePasParcours() {
    if (confirm('Voulez-vous vraiment recopier générer la structure de la formation ? ')) {
      await saveData(this.urlGenereStructureValue)
      callOut('Structure générée.', 'success')
      // todo: afficher le lien pour afficher le parcours par défaut
      // rediriger vers le parcours par défaut
      window.location.href = `${this.urlReloadValue} ? step = 3`
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
