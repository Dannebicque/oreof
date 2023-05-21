/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/notification_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 21/05/2023 14:08
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    url: String,
  }

  connect() {
    console.log('Hello, Stimulus!')
  }

  async lu(event) {
    const li = event.currentTarget
    const params = new URLSearchParams({ id: event.params.id })
    await fetch(`${this.urlValue}?${params.toString()}`).then((e) => {
      if (e.status === 200) {
        // supprimer la classe non-lu sur le parent
        li.classList.remove('non-lu')

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
}
