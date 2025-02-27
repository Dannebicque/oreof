/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/js/base/init.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 21/01/2023 20:24
 */

/**
 *
 * Init.js
 *
 * Shows the template after initialization of the settings, nav, variables and common plugins.
 *
 *
 */
import {Variables} from './globals'
import Globals from './globals'
import Settings from './settings'
import Nav from './nav'

(function () {
  window.addEventListener('DOMContentLoaded', () => {
    // Variables to hold component instances that may require an update after certain events

    // Settings initialization
    if (typeof Settings !== 'undefined') {
      const settings = new Settings({attributes: {placement: 'horizontal'}, showSettings: true, storagePrefix: 'acorn-standard-'});
    }

    // Variables initialization of Globals.js file which contains valus from css
    if (typeof Variables !== 'undefined') {
      const variables = new Variables();
    }

    // Initializing component and plugin classes
    function initBase() {
      // Should be before everything
      if (typeof Nav !== 'undefined') {
        const nav = new Nav(document.getElementById('nav'));
      }
    }

    // Initializing of scripts.js file
    function initScripts() {
      if (typeof Scripts !== 'undefined') {
        const scripts = new Scripts();
      }
    }

    document.documentElement.addEventListener(Globals.menuPlacementChange, (event) => {
      setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
      }, 25);
    });

    document.documentElement.addEventListener(Globals.layoutChange, (event) => {
      setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
      }, 25);
    });

    document.documentElement.addEventListener(Globals.menuBehaviourChange, (event) => {
      setTimeout(() => {
        window.dispatchEvent(new Event('resize'));
      }, 25);
    });

    // Showing the template after waiting for a bit so that the css variables are all set
    setTimeout(() => {
      document.documentElement.setAttribute('data-show', 'true');
      document.body.classList.remove('spinner');
      initBase();
      initScripts();
    }, 200);
  });
})();
