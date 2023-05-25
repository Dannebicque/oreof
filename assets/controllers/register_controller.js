/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/register_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 13/03/2023 21:14
 */

import { Controller } from '@hotwired/stimulus'
import TomSelect from 'tom-select'

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
    const tom = new TomSelect(this.selectMention)
    const tomDroits = new TomSelect(this.selectDroits)
  }

  changeCentre(event) {
    const val = event.target.value
    if (val === 'cg_etablissement' || parseInt(val, 10) === 1) {
      const tom = this.selectMention.tomselect
      tom.clear()
      tom.disable()
      this._updateSelectDroit('cg_etablissement')
      document.getElementById('selectListe').classList.add('d-none')
    } else if (val === 'cg_composante' || parseInt(val, 10) === 0) {
      this._updateSelect(this.urlComposanteValue)
      this._updateSelectDroit('cg_composante')

      document.getElementById('selectListe').classList.remove('d-none')
    } else if (val === 'cg_formation') {
      this._updateSelect(this.urlFormationValue)
      this._updateSelectDroit('cg_formation')
      document.getElementById('selectListe').classList.remove('d-none')
    }
  }

  async _updateSelectDroit(centre) {
    await fetch(`${this.urlDroitsValue}?centre=${centre}`).then((response) => response.json()).then(
      (data) => {
        const items = data
        const tom = this.selectDroits.tomselect
        const tab = []

        items.forEach((mention) => {
          tab.push({ value: mention.id, text: mention.libelle })
        })

        tom.clear()
        tom.clearOptions()
        tom.enable()

        tom.addOptions(tab)
        tom.settings.placeholder = 'Choisir dans la liste'
        tom.inputState()
      },
    )
  }

  async _updateSelect(url) {
    await fetch(url).then((response) => response.json()).then(
      (data) => {
        const items = data
        const tom = this.selectMention.tomselect
        const tab = []

        items.forEach((mention) => {
          tab.push({ value: mention.id, text: mention.libelle })
        })

        tom.clear()
        tom.clearOptions()
        tom.enable()

        tom.addOptions(tab)
        tom.settings.placeholder = 'Choisir dans la liste'
        tom.inputState()
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
