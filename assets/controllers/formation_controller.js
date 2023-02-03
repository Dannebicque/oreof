import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = [
    'user',
  ]

  static values = {
    url: String,
    urlUser: String,
  }

  connect() {
    this._updateListeMention(document.getElementById('formation_ses_typeDiplome').value, document.getElementById('formation_ses_domaine').value)
  }

  changeInscriptionRNCP(event) {
    const inscriptionRNCP = event.target.value
    if (parseInt(inscriptionRNCP) === 1) {
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

  async changeResponsableMention(event) {
    const responsableMention = event.target.value
    const reponse = await fetch(`${this.urlUserValue}?id=${responsableMention}`)
    this.userTarget.innerHTML = await reponse.text()
  }

  async _updateListeMention(typeDiplome, domaine) {
    await fetch(`${this.urlValue}?typeDiplome=${typeDiplome}&domaine=${domaine}`).then((response) => response.json()).then(
      (data) => {
        const { mentions } = data
        const selectMention = document.getElementById('formation_ses_mention')
        selectMention.innerHTML = ''

        mentions.forEach((mention) => {
          const option = document.createElement('option')
          option.value = mention.id
          option.text = mention.libelle
          selectMention.appendChild(option)
        })
      },
    )
  }
}
