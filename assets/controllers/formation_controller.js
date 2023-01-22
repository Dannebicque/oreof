import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'
import { addCallout } from '../js/callOut'

export default class extends Controller {
  static targets = []
  static values = {
    url: String,
  }

  connect() {
    this._updateListeMention(document.getElementById('formation_ses_typeDiplome').value, document.getElementById('formation_ses_domaine').value)

  }

  changeInscriptionRNCP(event) {
    const inscriptionRNCP = event.target.value
    console.log(inscriptionRNCP)
    if (1 === parseInt(inscriptionRNCP)) {
      document.getElementById('formation_ses_codeRNCP').disabled = false
    } else {
      document.getElementById('formation_ses_codeRNCP').disabled = true
    }
  }

  changeTypeDiplome(event) {
    this._updateListeMention(event.target.value, document.getElementById('formation_ses_domaine').value)
  }

  changeDomaine(event) {
    this._updateListeMention(document.getElementById('formation_ses_typeDiplome').value, event.target.value)
  }

  async _updateListeMention(typeDiplome, domaine) {
    console.log(typeDiplome, domaine)
    await fetch(this.urlValue + '?typeDiplome=' + typeDiplome + '&domaine=' + domaine).then(response => response.json()).then(
      data => {
        console.log(data)
        const mentions = data.mentions
        let selectMention = document.getElementById('formation_ses_mention')
        selectMention.innerHTML = ''

        mentions.forEach(mention => {
          let option = document.createElement('option')
          option.value = mention.id
          option.text = mention.libelle
          selectMention.appendChild(option)
        })
      }
    )
  }
}
