import * as bootstrap from 'bootstrap'

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

window.da = {
  loaderStimulus: '<div class="loader-stimulus text-center">... Chargement en cours ...</div>',
  loader: document.getElementById('loader'),
}

window.addEventListener('load', () => { // le dom est chargé
  // toast
  toasts.forEach((toast) => {
    callOut(toast.text, toast.type)
  })
})
