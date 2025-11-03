/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/email_template_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/10/2025 19:00
 */

import { Controller } from '@hotwired/stimulus'

// Simple debounce
function debounce (fn, delay = 350) {
  let t
  return (...args) => {
    clearTimeout(t)
    t = setTimeout(() => fn(...args), delay)
  }
}

export default class extends Controller {
  static targets = [
    'subject', 'bodyHtml', 'bodyText', 'previewSubject', 'previewIframe', 'previewText', 'workflow'
  ]

  get previewUrl () {
    // même domaine, route définie côté contrôleur
    return '/administration/config/email/preview/render'
  }

  connect () {
    this.updatePreview = debounce(this.updatePreview.bind(this), 300);
    // Live preview on input
    ['subject', 'bodyHtml', 'bodyText', 'workflow'].forEach(t => {
      this[`${t}Target`].addEventListener('input', this.updatePreview)
      this[`${t}Target`].addEventListener('change', this.updatePreview)
    })
    this.updatePreview()
  }

  insertToken (e) {
    const token = e.currentTarget.dataset.tokenValue
    // insère au curseur dans bodyHtml
    const ta = this.bodyHtmlTarget
    const start = ta.selectionStart ?? ta.value.length
    const end = ta.selectionEnd ?? ta.value.length
    ta.value = ta.value.slice(0, start) + token + ta.value.slice(end)
    ta.focus()
    ta.selectionStart = ta.selectionEnd = start + token.length
    this.updatePreview()
  }

  async updatePreview () {
    const payload = new FormData()
    payload.set('subject', this.subjectTarget?.value ?? '')
    payload.set('bodyHtml', this.bodyHtmlTarget?.value ?? '')
    payload.set('bodyText', this.bodyTextTarget?.value ?? '')
    payload.set('workflow', this.workflowTarget?.value ?? 'preview.workflow')
    // Optionnel: payload.set("overrides", JSON.stringify({ user: { fullName: "Bob" } }));

    try {
      const res = await fetch(this.previewUrl, { method: 'POST', body: payload })
      if (!res.ok) throw new Error('Preview HTTP ' + res.status)
      const data = await res.json()
      this.previewSubjectTarget.textContent = data.subject ?? ''

      // injecte HTML rendu dans l'iframe
      const doc = this.previewIframeTarget.contentDocument || this.previewIframeTarget.contentWindow.document
      doc.open()
      doc.write(data.html ?? '')
      doc.close()

      this.previewTextTarget.textContent = data.text ?? ''
    } catch (e) {
      this.previewSubjectTarget.textContent = 'Erreur de preview'
      this.previewTextTarget.textContent = String(e)
    }
  }
}
