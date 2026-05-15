/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/tooltip_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/02/2023 18:06
 */

import { Controller } from '@hotwired/stimulus'
import { Tooltip } from 'bootstrap'

export default class extends Controller {
  static values = {
    placement: {
      type: String,
      default: 'bottom',
    },
    trigger: String,
    customClass: String,
    title: String,
    container: {
      type: String,
      default: 'body',
    },
    html: Boolean,
  }

  connect() {
    const title = this.hasTitleValue
      ? this.titleValue
      : this.element.getAttribute('data-bs-title') || this.element.getAttribute('title')

    const customClass = this.hasCustomClassValue
      ? this.customClassValue
      : this.element.getAttribute('data-bs-custom-class')

    const container = this.hasContainerValue
      ? this.containerValue
      : this.element.getAttribute('data-bs-container')

    const options = {
      trigger: this.hasTriggerValue ? this.triggerValue : (this.element.getAttribute('data-bs-trigger') || 'hover focus'),
      html: this.hasHtmlValue ? this.htmlValue : this.element.getAttribute('data-bs-html') === 'true',
      placement: this.hasPlacementValue ? this.placementValue : (this.element.getAttribute('data-bs-placement') || 'top'),
    }

    if (customClass && customClass.trim() !== '') {
      options.customClass = customClass
    }

    if (container && container.trim() !== '') {
      options.container = container
    } else {
      options.container = 'body'
    }

    if (title && title.trim() !== '') {
      options.title = title
    }

    this.tooltip = Tooltip.getOrCreateInstance(this.element, options)
  }

  disconnect () {
    if (this.tooltip) {
      this.tooltip.dispose()
      this.tooltip = null
    }
  }
}
