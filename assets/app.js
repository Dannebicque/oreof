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
import 'trix'

import callOut from './js/callOut'
// import './styles/legacy.scss';
import './styles/app.css'
import './styles/_timeline.scss'

import './bootstrap'


import './js/base/init'
import './js/toggle'

document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(
  el => new bootstrap.Tooltip(el)
)


window.addEventListener('load', () => { // le dom est chargé
  const savedTheme = localStorage.getItem('oreof-theme')
  if (savedTheme === 'dark' || savedTheme === 'light') {
    document.documentElement.setAttribute('data-theme', savedTheme)
  }

  const toastQueue = Array.isArray(window.toasts) ? window.toasts : []
  // toast
  toastQueue.forEach((toast) => {
    callOut(toast.text, toast.type)
  })

  document.addEventListener('trix-before-initialize', () => {
  })
})
