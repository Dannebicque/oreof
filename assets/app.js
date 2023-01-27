/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
// import * as bootstrap from 'bootstrap'

import { addCallout } from './js/callOut'

window.da = {
  loaderStimulus: '<div class="loader-stimulus text-center">... Chargement en cours ...</div>',
  loader: document.getElementById('loader'),
}
import './styles/app.scss';

// start the Stimulus application
import './bootstrap'


import './js/vendor/OverlayScrollbars.min'
import './js/vendor/clamp.min'

import './js/base/init'
import './js/common'
import './js/scripts'

window.addEventListener('load', () => { // le dom est chargé
  //toast
  toasts.forEach((toast) => {
    addCallout(toast.text, toast.type)
  })
})
