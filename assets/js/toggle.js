// javascript (fichier : `toggle.js`)
(function () {
  const DEFAULT_CLASS = 'hidden'
  const DEFAULT_ICON_OPEN = 'fa-chevron-up'
  const DEFAULT_ICON_CLOSE = 'fa-chevron-down'

  function findTrigger (el) {
    return el.closest('[data-toggle]')
  }

  function getTarget (trigger) {
    const selector = trigger.getAttribute('data-target')
    if (selector) {
      try {
        return document.querySelector(selector)
      } catch {
        return null
      }
    }
    return trigger.nextElementSibling || null
  }

  function getToggleClass (trigger) {
    return trigger.getAttribute('data-toggle-class') || DEFAULT_CLASS
  }

  function setAria (trigger, expanded) {
    if (!trigger) return
    trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false')
  }

  function hideElement (el, cls) {
    if (!el) return
    el.classList.add(cls)
    el.setAttribute('aria-hidden', 'true')
  }

  function showElement (el, cls) {
    if (!el) return
    el.classList.remove(cls)
    el.setAttribute('aria-hidden', 'false')
  }

  function toggleElement (el, cls) {
    if (!el) return
    const isHidden = el.classList.contains(cls)
    if (isHidden) {
      showElement(el, cls)
    } else {
      hideElement(el, cls)
    }
    return !isHidden
  }

  // function collapseGroup (trigger, keepOpenSelector) {
  //   const group = trigger.getAttribute('data-toggle-group')
  //   if (!group) return
  //   const cls = getToggleClass(trigger)
  //   document.querySelectorAll(`[data-toggle-group="${group}"]`).forEach(t => {
  //     const target = getTarget(t)
  //     if (!target) return
  //     if (keepOpenSelector && target.matches(keepOpenSelector)) return
  //     hideElement(target, cls)
  //     setAria(t, false)
  //     updateIconState(t, false)
  //   })
  // }

  function findIconElement (trigger, target) {
    // fallback: prefer an <i> inside the trigger, else an <i> inside the target
    return trigger.querySelector('i.data-toggle-icon') || (target && target.querySelector('i.data-toggle-icon')) || null
  }

  function collapseGroup (trigger) {
    const group = trigger.getAttribute('data-toggle-group')
    if (!group) return
    const cls = getToggleClass(trigger)
    document.querySelectorAll(`[data-toggle-group="${group}"]`).forEach(t => {
      if (t === trigger) return
      const target = getTarget(t)
      if (!target) return
      hideElement(target, cls)
      setAria(t, false)
      updateIconState(t, false)
    })
  }

  function parseClasses (attrValue, fallback) {
    if (!attrValue) return fallback.split(' ').filter(Boolean)
    return attrValue.split(' ').map(s => s.trim()).filter(Boolean)
  }

  function updateIcon (trigger, opened) {
    const target = getTarget(trigger)
    const iconEl = findIconElement(trigger, target)
    if (!iconEl) return

    const openClasses = parseClasses(trigger.getAttribute('data-toggle-icon-open'), DEFAULT_ICON_OPEN)
    const closeClasses = parseClasses(trigger.getAttribute('data-toggle-icon-close'), DEFAULT_ICON_CLOSE)

    openClasses.forEach(c => iconEl.classList.remove(c))
    closeClasses.forEach(c => iconEl.classList.remove(c))
    const apply = opened ? openClasses : closeClasses
    apply.forEach(c => iconEl.classList.add(c))
  }

  function updateIconState (trigger, opened) {
    try {
      updateIcon(trigger, opened)
    } catch { /* empty */ }
  }

  document.addEventListener('click', (e) => {
    const trigger = findTrigger(e.target)
    if (!trigger) return
    e.preventDefault()
    const target = getTarget(trigger)
    if (!target) return
    const cls = getToggleClass(trigger)

    // collapseGroup(trigger, target.matches(':not(*)') ? null : target)
    collapseGroup(trigger)
    const opened = toggleElement(target, cls)
    setAria(trigger, opened)
    updateIconState(trigger, opened)
  }, true)

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      const trigger = findTrigger(e.target)
      if (!trigger) return
      e.preventDefault()
      trigger.click()
    }
  }, true)

  // API publique (conserve l'API existante)
  window.Toggle = {
    show (selectorOrEl, cls = DEFAULT_CLASS) {
      const el = typeof selectorOrEl === 'string' ? document.querySelector(selectorOrEl) : selectorOrEl
      showElement(el, cls)
    },
    hide (selectorOrEl, cls = DEFAULT_CLASS) {
      const el = typeof selectorOrEl === 'string' ? document.querySelector(selectorOrEl) : selectorOrEl
      hideElement(el, cls)
    },
    toggle (selectorOrEl, cls = DEFAULT_CLASS) {
      const el = typeof selectorOrEl === 'string' ? document.querySelector(selectorOrEl) : selectorOrEl
      return toggleElement(el, cls)
    }
  }
})()
