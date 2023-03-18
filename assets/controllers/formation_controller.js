/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/formation_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/02/2023 16:43
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = [
    'user',
  ]

  static values = {
    url: String,
    urlListePersonnel: String,
    urlUser: String,
  }

  connect() {
    this._updateListeMention(document.getElementById('formation_ses_typeDiplome').value, document.getElementById('formation_ses_domaine').value).then(() => {
      const mention = document.getElementById('formation_ses_mention')
      const mentionTexte = document.getElementById('formation_ses_mentionTexte')

      mentionTexte.disabled = mention.value !== 'autre' && mention.value.trim() !== 'null';

      if (mentionTexte.value.trim() !== '') {
        mention.value = 'autre'
      }
    })
    this._updateListePersonnel(document.getElementById('formation_ses_composantePorteuse').value).then(() => {
      if (document.getElementById('formation_ses_responsableMention').value !== 'null') {
        this._updatePersonnel(document.getElementById('formation_ses_responsableMention').value)
      }
    })
  }

  changeInscriptionRNCP(event) {
    const inscriptionRNCP = event.target.value
    if (parseInt(inscriptionRNCP, 10) === 1) {
      document.getElementById('formation_ses_codeRNCP').disabled = false
    } else {
      document.getElementById('formation_ses_codeRNCP').disabled = true
    }
  }

  changeComposante(event) {
    this._updateListePersonnel(event.target.value)
  }

  changeTypeDiplome(event) {
    this._updateListeMention(event.target.value, document.getElementById('formation_ses_domaine').value)
  }

  changeDomaine(event) {
    this._updateListeMention(document.getElementById('formation_ses_typeDiplome').value, event.target.value)
  }

  changeResponsableMention(event) {
    this._updatePersonnel(event.target.value)
  }

  async _updatePersonnel(id) {
    const responsableMention = id
    const reponse = await fetch(`${this.urlUserValue}?id=${responsableMention}`)
    this.userTarget.innerHTML = await reponse.text()
  }

  changeMention(event) {
    if (event.target.value === 'autre' || event.target.value.trim() === 'null') {
      document.getElementById('formation_ses_mentionTexte').disabled = false
    } else {
      document.getElementById('formation_ses_mentionTexte').disabled = true
    }
  }

  changeMentionTexte(event) {
    if (event.target.value.trim() !== '') {
      document.getElementById('formation_ses_mention').value = 'autre'
    }
  }

  async _updateListePersonnel(composante) {
    await fetch(`${this.urlListePersonnelValue}?composante=${composante}`).then((response) => response.json()).then(
      (data) => {
        const selectPersonnels = document.getElementById('formation_ses_responsableMention')
        selectPersonnels.innerHTML = ''

        let option = document.createElement('option')
        option.value = null
        option.text = ''
        selectPersonnels.appendChild(option)

        data.forEach((personnel) => {
          option = document.createElement('option')
          option.value = personnel.id
          option.text = personnel.libelle
          selectPersonnels.appendChild(option)
        })
      },
    )
  }

  async _updateListeMention(typeDiplome, domaine) {
    await fetch(`${this.urlValue}?typeDiplome=${typeDiplome}&domaine=${domaine}`).then((response) => response.json()).then(
      (data) => {
        const { mentions } = data
        const selectMention = document.getElementById('formation_ses_mention')
        selectMention.innerHTML = ''

        let option = document.createElement('option')
        option.value = null
        option.text = ''
        selectMention.appendChild(option)

        option = document.createElement('option')
        option.value = 'autre'
        option.text = 'Mention hors nomenclature, je complète la zone de saisie'
        selectMention.appendChild(option)

        mentions.forEach((mention) => {
          option = document.createElement('option')
          option.value = mention.id
          option.text = mention.libelle
          selectMention.appendChild(option)
        })
      },
    )
  }
}
