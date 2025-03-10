/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/crud_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/03/2023 09:57
 */

import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'
import { useDebounce } from 'stimulus-use'
import callOut from '../js/callOut'
import updateUrl from '../js/updateUrl'

export default class extends Controller {
  static targets = ['liste']

  static values = { url: String, page: Number }

  static debounces = ['rechercher']

  fields = {}

  scrollPosition = 0

  connect() {
    useDebounce(this)
    this.fields = {
      page: this.pageValue ?? 1,
    }
    this._updateListe({
      page: this.pageValue,
    })
  }

  filter(event) {
    if (event.target.value === '') {
      delete this.fields[event.params.field]
    } else {
      this.fields[event.params.field] = event.target.value
    }
    this._updateListe(this.fields)
  }

  page(event) {
    this.fields.page = event.params.page
    updateUrl({ page: event.params.page })
    this._updateListe(this.fields)
  }

  rechercher(event) {
    event.preventDefault()
    this.fields.q = event.target.value
    this._updateListe(this.fields)
  }

  effaceFiltre(event) {
    event.preventDefault()
    this.fields = {}
    document.getElementById('filtre_crud').value = ''
    this._updateListe(this.fields)
  }

  delete(event) {
    event.preventDefault()
    const { url } = event.params
    const { csrf } = event.params
    let modal = new Modal(document.getElementById('modal-delete'))
    modal.show()
    const btn = document.getElementById('btn-confirm-supprimer')
    btn.replaceWith(btn.cloneNode(true))
    document.getElementById('btn-confirm-supprimer').addEventListener('click', async () => {
      const body = {
        method: 'DELETE',
        body: JSON.stringify({
          csrf,
        }),
      }
      modal = null
      await fetch(url, body).then(async (e) => {
        if (e.status === 200) {
          callOut('Suppression effectuée', 'success')
          console.log(this.fields)
          this._updateListe(this.fields)
        } else {
          const data = await e.json()
          if (data.message !== undefined && data.message.trim() !== '') {
            callOut(data.message, 'danger')
          } else {
            callOut('Erreur lors de la suppression', 'danger')
          }
        }
      })
    })
    modal = null
  }

  async duplicate(event) {
    event.preventDefault()
    const { url } = event.params
    await fetch(url).then(() => {
      callOut('Duplication effectuée', 'success')
      this._updateListe()
    })
  }

  refreshListe() {
    this._updateListe()
  }

  async _updateListe(params) {
    this.scrollPosition = window.scrollY
    const _params = new URLSearchParams(params)
    this.listeTarget.innerHTML = window.da.loaderStimulus
    const response = await fetch(`${this.urlValue}?${_params.toString()}`)
    this.listeTarget.innerHTML = await response.text()
    window.scrollTo(0, this.scrollPosition)
  }

  sort(event) {
    this.fields.sort = event.params.sort
    this.fields.direction = event.params.direction
    this._updateListe(this.fields)
  }
}
