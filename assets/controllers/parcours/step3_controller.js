import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'
import callOut from '../../js/callOut'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { etatStep } from '../../js/etatStep'

export default class extends Controller {
  static targets = [
    'liste',
  ]

  static values = {
    url: String,
    urlListe: String,
  }

  etatStep(event) {
    etatStep(this.urlValue, 3, event, 'parcours')
    // this._save({
    //   action: 'etatStep',
    //   value: 3,
    //   isChecked: event.target.checked,
    // })
    //
    // const parent = event.target.closest('.alert')
    // if (event.target.checked) {
    //   parent.classList.remove('alert-warning')
    //   parent.classList.add('alert-success')
    // } else {
    //   parent.classList.remove('alert-success')
    //   parent.classList.add('alert-warning')
    // }
  }

  connect() {
    this._updateListe()
  }

  delete(event) {
    event.preventDefault()
    const { url } = event.params
    const { csrf } = event.params
    let modal = new Modal(document.getElementById('modal-delete'))
    modal.show()
    document.getElementById('btn-confirm-supprimer').addEventListener('click', async () => {
      const body = {
        method: 'DELETE',
        body: JSON.stringify({
          csrf,
        }),
      }
      modal = null
      await fetch(url, body).then(() => {
        callOut('Suppression effectuée', 'success')
        this._updateListe()
      })
    })
  }

  async deplacerBcc(event) {
    event.preventDefault()
    const { url } = event.params
    await fetch(url).then(() => {
      callOut('Bloc de compétence déplacé', 'success')
      this._updateListe()
    })
  }

  async deplacerCc(event) {
    event.preventDefault()
    const { url } = event.params
    await fetch(url).then(() => {
      callOut('Compétence déplacée', 'success')
      this._updateListe()
    })
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

  recopieBcc() {
    const elt = document.getElementById('parcoursSource')
    const nameParcours = elt.options[elt.selectedIndex].text
    if (confirm(`Voulez-vous vraiment recopier les BCC du parcours "${nameParcours}" ? `)) {
      this._save({
        action: 'recopieBcc',
        value: document.getElementById('parcoursSource').value,
      })
      callOut('Recopie effectuée.', 'success')
      this._updateListe()
    }
  }

  async _updateListe() {
    this.listeTarget.innerHTML = window.da.loaderStimulus
    const response = await fetch(this.urlListeValue)
    this.listeTarget.innerHTML = await response.text()
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet3', 'parcours')
    })
  }
}
