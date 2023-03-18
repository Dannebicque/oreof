/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/js/scripts.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 21/01/2023 20:20
 */

import Nav from './base/nav'

class Scripts {
  constructor() {
    this._init();
  }

  // Showing the template after waiting for a bit so that the css variables are all set
  // Initialization of the common scripts and page specific ones
  _init() {
    setTimeout(() => {
      document.documentElement.setAttribute('data-show', 'true');
      this._initBase();
      this._initCommon();
    }, 100);
  }

  // Base scripts initialization
  _initBase() {
    // Navigation
    if (typeof Nav !== 'undefined') {
      const nav = new Nav(document.getElementById('nav'));
    }
  }

  // Common plugins and overrides initialization
  _initCommon() {
    // common.js initialization
    if (typeof Common !== 'undefined') {
      let common = new Common();
    }
  }
}
