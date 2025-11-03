/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/app.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 14/03/2023 22:21
 */

window.da = {
  loaderStimulus: '<div class="loader-stimulus text-center">... Chargement en cours ...</div>',
  loader: document.getElementById('loader'),
}

import * as bootstrap from 'bootstrap'
import Trix from 'trix'

import callOut from './js/callOut'
import './styles/app.scss';

import './bootstrap'

import './js/vendor/OverlayScrollbars.min'
import './js/vendor/clamp.min'

import './js/base/init'
import './js/common'
import './js/scripts'

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))


window.addEventListener('load', () => { // le dom est chargé
  document.getElementsByTagName('html')[0].dataset.color = localStorage.getItem('acorn-standard-color') ?? 'light-blue'
  // toast
  toasts.forEach((toast) => {
    callOut(toast.text, toast.type)
  })

  document.addEventListener('trix-before-initialize', () => {
  })
})
