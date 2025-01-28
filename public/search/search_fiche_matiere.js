document.addEventListener('DOMContentLoaded', async (e) => {
  let currentPage = 1

  const dataObject = document.querySelector('#dataFicheMatiereSearch')

  const totalNumber = Number(dataObject.getAttribute('data-nb-fiches-total'))
  const totalPageNumber = Math.floor((totalNumber / 30) + 1)
  const keyword = dataObject.getAttribute('data-keyword')
  const fetchUrl = dataObject.getAttribute('data-fetch-url')
  const parcoursViewUrl = dataObject.getAttribute('data-parcours-view-url')
  const ficheMatiereViewUrl = dataObject.getAttribute('data-fiche-matiere-view-url')

  const buttonPageRight = document.querySelector('i.button-page-right')
  const buttonPageLeft = document.querySelector('i.button-page-left')
  const buttonGoToPage = document.querySelector('.button-go-to-page')

  const inputNumeroPage = document.querySelector('input[name=\'inputNumeroPage\']')

  if (totalNumber > 1) {
    // Affichage du résultat pour la page 1
    await displayResult(fetchUrl, currentPage, keyword, parcoursViewUrl, ficheMatiereViewUrl, totalPageNumber)
    /**
     * Navigation vers la page souhaitée
     */
    buttonPageLeft.addEventListener('click', async (e) => {
      if (currentPage > 1) {
        currentPage -= 1
        await displayResult(fetchUrl, currentPage, keyword, parcoursViewUrl, ficheMatiereViewUrl, totalPageNumber)
      }
    })

    buttonPageRight.addEventListener('click', async (e) => {
      if (currentPage < totalPageNumber) {
        currentPage += 1
        await displayResult(fetchUrl, currentPage, keyword, parcoursViewUrl, ficheMatiereViewUrl, totalPageNumber)
      }
    })

    buttonGoToPage.addEventListener('click', async (e) => {
      let value = Number(inputNumeroPage.value)
      if (Number.isInteger(value) === false || value < 1) {
        value = 1
      }
      if (value > totalPageNumber) {
        value = totalPageNumber
      }
      currentPage = value
      inputNumeroPage.value = value
      await displayResult(fetchUrl, currentPage, keyword, parcoursViewUrl, ficheMatiereViewUrl, totalPageNumber)
    })
    /** ********************************** */
  }
})

async function displayResult(fetchUrl, pageNumber, keyword, parcoursViewUrl, ficheMatiereViewUrl, totalPageNumber) {
  emptyResultList()
  displayLoadingIcon()
  const url = configureFetchUrl(fetchUrl, pageNumber, keyword)
  const result = await fetchResultPage(url)
  if (result) {
    hideLoadingIcon()
    updateDomWithResult(result, parcoursViewUrl, ficheMatiereViewUrl, keyword)
    updatePageLabel(pageNumber, totalPageNumber)
  }
}

function updatePageLabel(pageNumber, totalPageNumber) {
  const label = document.querySelector('.numero-page')
  label.textContent = `Page ${pageNumber} / ${totalPageNumber}`
}

async function fetchResultPage(url) {
  return await fetch(url)
    .then((response) => response.json())
    .catch((error) => console.error(error))
}

function configureFetchUrl(baseUrl, pageNumber, keyword) {
  let url = baseUrl.replace(/1234567890/, pageNumber)
  url = url.replace('%C2%B5%23+', keyword)

  return url
}

function updateDomWithResult(jsonResult, parcoursViewUrl, ficheMatiereViewUrl, keyword) {
  const rootNode = document.querySelector('.rootNodeForFicheMatiereList')

  jsonResult.forEach((fiche) => {
    const row = document.createElement('div')
    row.classList.add('row', 'my-3', 'py-3', 'px-2', 'border', 'border-primary', 'rounded')

    const ficheMatiereTitle = document.createElement('div')
    ficheMatiereTitle.classList.add('col-4')

    const ficheMatiereTitleDiv = document.createElement('div')
    ficheMatiereTitleDiv.classList.add('col-12')

    const libelleFicheDiv = document.createElement('div');
    libelleFicheDiv.classList.add('col-12');
    let libelleFiche = document.createElement('span');
    libelleFiche.classList.add('text-dark', 'font-weight-bold', 'mb-1');
    libelleFiche.textContent = 'Fiche matière';
    libelleFicheDiv.appendChild(libelleFiche);

    const ficheMatiereLibelle = document.createElement('a')
    ficheMatiereLibelle.textContent = fiche.fiche_matiere_libelle
    ficheMatiereLibelle.target = '_blank'
    ficheMatiereLibelle.setAttribute('href', ficheMatiereViewUrl.replace('%C2%B5%25%24%C2%A3', fiche.fiche_matiere_slug))
    ficheMatiereTitleDiv.appendChild(ficheMatiereLibelle)

    const ficheMatierePillDiv = document.createElement('div')
    ficheMatierePillDiv.classList.add('col-12', 'mt-3')

    ficheMatiereTitle.appendChild(libelleFicheDiv);
    ficheMatiereTitle.appendChild(ficheMatiereTitleDiv)
    ficheMatiereTitle.appendChild(ficheMatierePillDiv)

    if (isStringContainingText(fiche.fiche_matiere_objectifs, keyword)) {
      const objectifsPill = displayBadge('Objectifs', 'warning')
      ficheMatierePillDiv.appendChild(objectifsPill)
    }
    if (isStringContainingText(fiche.fiche_matiere_description, keyword)) {
      const descriptionPill = displayBadge('Description', 'info')
      ficheMatierePillDiv.appendChild(descriptionPill)
    }

    const parcoursTitle = document.createElement('div')
    parcoursTitle.classList.add('col-8')

    const parcoursLibelle = document.createElement('a')
    parcoursLibelle.textContent = `
            ${fiche.type_diplome_libelle ? `${fiche.type_diplome_libelle} - ` : ''}
            ${fiche.mention_libelle} - ${fiche.parcours_libelle} ${fiche.parcours_sigle ? `(${fiche.parcours_sigle})` : ''}
        `
    parcoursLibelle.target = '_blank'
    parcoursLibelle.classList.add('text-primary', 'font-weight-bold')
    parcoursLibelle.setAttribute('href', parcoursViewUrl.replace('%C2%B5%25%24%C2%A3', fiche.parcours_id))

    parcoursTitle.appendChild(parcoursLibelle)

    row.appendChild(parcoursTitle)
    row.appendChild(ficheMatiereTitle)

    rootNode.appendChild(row)
  })
}

function displayLoadingIcon() {
  hideLoadingIcon()
  const loadingIcon = document.createElement('i')
  loadingIcon.className = 'fa-duotone fa-spinner spinning-icon mt-4'
  const rootNode = document.querySelector('.loading-icon')
  rootNode.appendChild(loadingIcon)
}

function hideLoadingIcon() {
  if (document.querySelector('.spinning-icon')) {
    document.querySelector('.spinning-icon').remove()
  }
}

function emptyResultList() {
  const rootNode = document.querySelector('.rootNodeForFicheMatiereList')
  while (rootNode.hasChildNodes()) {
    rootNode.removeChild(rootNode.firstChild)
  }
}

function isStringContainingText(string, needle) {
  if (string) {
    return string.toUpperCase().includes(
      needle.toUpperCase(),
    )
  }

  return false
}

function displayBadge(text, color) {
  const pill = document.createElement('span')
  pill.classList.add('badge', 'rounded-pill', `text-bg-${color}`)
  pill.textContent = text

  return pill
}
