/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/structure/semestre_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 05/02/2023 13:01
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../../js/callOut';
import JsonResponse from '../../js/JsonResponse'
import updateUrl from '../../js/updateUrl'

export default class extends Controller {
  static targets = ['detail']

  static values = {
    semestreAffiche: String,
    ueAffichee: String,
    semestre: String,
    url: String,
  }

  async connect() {
    if (this.semestreAfficheValue === this.semestreValue) {
      const btn = document.getElementById(`btn_semestre_detail_${this.semestreValue}`)
      const response = await fetch(this.urlValue)
      this.detailTarget.innerHTML = await response.text()
      btn.dataset.state = 'open'
      btn.firstElementChild.classList.remove('fa-caret-right')
      btn.firstElementChild.classList.add('fa-caret-down')
      document.getElementById(`detail_semestre_${this.semestreValue}`).classList.remove('d-none')
    }
  }

  async detail(event) {
    if (event.target.dataset.state === 'open') {
      document.getElementById(`detail_semestre_${event.params.semestre}`).classList.add('d-none')
      this.detailTarget.innerHTML = ''
      event.target.dataset.state = 'close'
      event.target.firstElementChild.classList.add('fa-caret-right')
      event.target.firstElementChild.classList.remove('fa-caret-down')
      updateUrl({ semestre: event.params.semestre, ue: 0 }, 'remove')
    } else {
      const response = await fetch(event.params.url)
      this.detailTarget.innerHTML = await response.text()
      document.getElementById(`detail_semestre_${event.params.semestre}`).classList.remove('d-none')
      event.target.dataset.state = 'open'
      event.target.firstElementChild.classList.remove('fa-caret-right')
      event.target.firstElementChild.classList.add('fa-caret-down')
      updateUrl({ semestre: event.params.semestre })
    }
  }

  async deplacerSemestre(event) {
    event.preventDefault()
    const { url } = event.params
    await fetch(url).then(() => {
      callOut('Semestre déplacé', 'success')
      this.dispatch('refreshListe')
    })
  }

  async refreshListe(event) {
    const response = await fetch(event.params.url)
    this.detailTarget.innerHTML = await response.text()
  }

  reinitSemestre(event) {
    if (confirm('Voulez-vous vraiment réinitialiser ce semestre ? Les données des UEs et les EC seront supprimées.')) {
      const { url } = event.params
      fetch(url).then((reponse) => {
        JsonResponse(reponse)
        this.dispatch('refreshListe')
      })
    }
  }

  transformeTroncCommun(event) {
    if (confirm('Voulez-vous vraiment définir ce semestre comme semestre tronc commun de l\'ensemble des parcours de la formation (hors semestre non dispensés)')) {
      const { url } = event.params
      fetch(url).then((reponse) => {
        JsonResponse(reponse)
        this.dispatch('refreshListe')
      })
    }
  }
}
