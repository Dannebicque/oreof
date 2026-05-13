/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * Nav, Settings et Globals supprimés → remplacés par Stimulus (nav_controller, settings_controller).
 * Ce fichier gère uniquement l'affichage initial du template.
 */

(function () {
  let initialized = false

  const showTemplate = () => {
    document.documentElement.setAttribute('data-show', 'true')
    document.body.classList.remove('spinner')
  }

  // Premier chargement : attendre quelques ms que les variables CSS soient prêtes
  window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
      showTemplate()
      initialized = true
    }, 150)
  })

  // Navigations Turbo suivantes : afficher immédiatement
  document.addEventListener('turbo:load', () => {
    if (initialized) {
      showTemplate()
    }
  })
})()
