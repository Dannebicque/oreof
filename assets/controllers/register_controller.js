/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/register_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 13/03/2023 21:14
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    urlComposante: String,
    urlFormation: String,
    urlDroits: String,
  }

  selectMention = null

  selectDroits = null

  connect() {
    this.selectMention = document.getElementById('selectListe')
    this.selectDroits = document.getElementById('droits')
  }

  changeCentre(event) {
    const val = event.target.value
    if (val === 'cg_etablissement' || parseInt(val, 10) === 1) {
      this.selectMention.innerHtml = ''
      this._updateSelectDroit('cg_etablissement')
      this.selectMention.classList.add('d-none')
    } else if (val === 'cg_composante' || parseInt(val, 10) === 0) {
      this._updateSelect(this.urlComposanteValue)
      this._updateSelectDroit('cg_composante')
      this.selectMention.classList.remove('d-none')
    } else if (val === 'cg_formation') {
      this._updateSelect(this.urlFormationValue)
      this._updateSelectDroit('cg_formation')
      this.selectMention.classList.remove('d-none')
    }
  }

  async _updateSelectDroit(centre) {
    // regarder si déjà un parametre ou pas
    console.log()

    if (this.urlDroitsValue.includes('?')) {
      this.urlDroitsValue = `${this.urlDroitsValue}&centre=${centre}`
    } else {
      this.urlDroitsValue = `${this.urlDroitsValue}?centre=${centre}`
    }

    await fetch(`${this.urlDroitsValue}`).then((response) => response.json()).then(
      (data) => {
        const items = data
        while (this.selectDroits.options.length > 0) {
          this.selectDroits.remove(0);
        }
        let option = document.createElement('option')
        option.value = null
        option.text = 'Choisir dans la liste'
        this.selectDroits.add(option, null)

        items.forEach((mention) => {
          option = document.createElement('option')
          option.value = mention.id
          option.text = mention.libelle
          this.selectDroits.add(option, null)
        })
      },
    )
  }

  async _updateSelect(url) {
    await fetch(url).then((response) => response.json()).then(
      (data) => {
        const items = data
        while (this.selectMention.options.length > 0) {
          this.selectMention.remove(0);
        }

        let option = document.createElement('option')
        option.value = null
        option.text = 'Choisir dans la liste'
        this.selectMention.add(option, null)

        items.forEach((mention) => {
          option = document.createElement('option')
          option.value = mention.id
          option.text = mention.libelle
          this.selectMention.add(option, null)
        })
      },
    )
  }

  async sauvegardeFormModal(event) {
    event.preventDefault()
    const { url } = event.params

    // submit des data
    const data = new FormData()
    data.append('user_ldap_email', document.getElementById('user_ldap_email').value)

    // fetch
    const response = await fetch(url, {
      method: 'POST',
      body: data,
    }).then((reponse) => reponse.json())

    if (response.success) {
      // fermer la modale
      this.dispatch('refreshModale', { detail: { url: response.url } })
    }
  }

  refuserAcces() {
    document.getElementById('motifRefus').classList.toggle('d-none')
  }

  confirmeRefusAcces(event) {
    const url = event.target.dataset.href
    const motif = document.getElementById('texteMotifRefus').value

    if (motif.length > 0) {
      const data = new FormData()
      data.append('motif', motif)

      fetch(url, {
        method: 'POST',
        body: data,
      }).then((reponse) => reponse.json()).then(
        this.dispatch('modalHide'),
      )
    } else {
      alert('Merci de saisir un motif')
    }
  }
}
