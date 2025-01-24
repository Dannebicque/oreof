/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/notification_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 21/05/2023 14:08
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../js/callOut'

export default class extends Controller {
  static values = {
    url: String,
  }

  static targets = ['liste']

  async lu(event) {
    const li = event.currentTarget
    const params = new URLSearchParams({ id: event.params.id })
    await fetch(`${this.urlValue}?${params.toString()}`).then((e) => {
      if (e.status === 200) {
        // supprimer la classe non-lu sur le parent
        li.classList.remove('non-lu')
        // modifier l'icone
        li.querySelector('i').classList.remove('fa-exclamation')
        li.querySelector('i').classList.remove('text-waning')
        li.querySelector('i').classList.add('fa-check')
        li.querySelector('i').classList.add('text-success')
        li.querySelector('i').parentElement.classList.remove('border-warning')
        li.querySelector('i').parentElement.classList.add('border-success')

        // compter le nombre restant
        const nb = document.querySelectorAll('.non-lu').length
        // si 0, supprimer le badge
        if (nb === 0) {
          document.getElementById('indicNotif').remove()
          document.getElementById('indicNotifBtn').classList.remove('new-notif')
        }
      }
    })
  }

  async toutSupprimer(event) {
    if (!confirm('Voulez-vous vraiment supprimer cette notification ?')) {
      return false
    }
    await fetch(`${event.params.url}`).then((e) => {
      callOut(e.status === 200 ? 'Suppression effectuée' : 'Erreur lors de la suppression', e.status === 200 ? 'success' : 'error')
      this.listeTarget.innerHTML = e.status === 200 ? '' : this.listeTarget.innerHTML
    })
  }

  async toutLu(event) {
    if (!confirm('Voulez-vous vraiment marquer toutes les notifications comme lues ?')) {
      return false
    }
    await fetch(`${event.params.url}`).then((e) => {
      callOut(e.status === 200 ? 'Mise à jour effectuée' : 'Erreur lors de la mise à jour', e.status === 200 ? 'success' : 'error')
      // changer les icones de toutes les notifications
      this.listeTarget.querySelectorAll('.non-lu').forEach((li) => {
        li.classList.remove('non-lu')
        // modifier l'icone
        li.querySelector('i').classList.remove('fa-exclamation')
        li.querySelector('i').classList.remove('text-waning')
        li.querySelector('i').classList.add('fa-check')
        li.querySelector('i').classList.add('text-success')
        li.querySelector('i').parentElement.classList.remove('border-warning')
        li.querySelector('i').parentElement.classList.add('border-success')
      })
    })
  }
}
