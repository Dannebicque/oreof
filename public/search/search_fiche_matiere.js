document.addEventListener('DOMContentLoaded', async (e) => {
    let currentPage = 1;

    let dataObject = document.querySelector('#dataFicheMatiereSearch');

    let totalNumber = Number(dataObject.getAttribute('data-nb-fiches-total'));
    let totalPageNumber = Math.floor( (totalNumber / 30) + 1);
    let keyword = dataObject.getAttribute('data-keyword');
    let fetchUrl = dataObject.getAttribute('data-fetch-url');
    let parcoursViewUrl = dataObject.getAttribute('data-parcours-view-url');
    let ficheMatiereViewUrl = dataObject.getAttribute('data-fiche-matiere-view-url');

    let buttonPageRight = document.querySelector('i.button-page-right');
    let buttonPageLeft = document.querySelector('i.button-page-left');

    // Affichage du résultat pour la page 1
    await displayResult(fetchUrl, currentPage, keyword, parcoursViewUrl, ficheMatiereViewUrl);

    /**
     * Navigation vers la page souhaitée
     */
    buttonPageLeft.addEventListener('click', async e => {
        if(currentPage > 1){
            currentPage -= 1;
            await displayResult(fetchUrl, currentPage, keyword, parcoursViewUrl, ficheMatiereViewUrl);
        }
    })

    buttonPageRight.addEventListener('click', async e => {
        if(currentPage < totalPageNumber){
            currentPage += 1;
            await displayResult(fetchUrl, currentPage, keyword, parcoursViewUrl, ficheMatiereViewUrl);
        }
    });
    /*************************************/
});


async function displayResult(fetchUrl, pageNumber, keyword, parcoursViewUrl, ficheMatiereViewUrl){
    emptyResultList();
    displayLoadingIcon();
    let url = configureFetchUrl(fetchUrl, pageNumber, keyword);
    let result = await fetchResultPage(url);
    if(result){
        hideLoadingIcon();
        updateDomWithResult(result, parcoursViewUrl, ficheMatiereViewUrl);
        updatePageLabel(pageNumber);
    }
}

function updatePageLabel(pageNumber){
    let label = document.querySelector('.numero-page');
    label.textContent = `Page ${pageNumber}`;
}

async function fetchResultPage(url){
    return await fetch(url)
        .then(response => response.json())
        .catch(error => console.error(error));
}

function configureFetchUrl(baseUrl, pageNumber, keyword){
    let url = baseUrl.replace(/1234567890/, pageNumber);
    url = url.replace("%C2%B5%23+", keyword);

    return url;
}

function updateDomWithResult(jsonResult, parcoursViewUrl, ficheMatiereViewUrl){
    let rootNode = document.querySelector(".rootNodeForFicheMatiereList");  

    jsonResult.forEach(fiche => {
        let row = document.createElement('div');
        row.classList.add("row", "my-3", "py-3", "px-2", "border", "border-primary", "rounded");

        let ficheMatiereTitle = document.createElement('div');
        ficheMatiereTitle.classList.add('col-4');
        
        let ficheMatiereLibelle = document.createElement('a');
        ficheMatiereLibelle.textContent = fiche.fiche_matiere_libelle;
        ficheMatiereLibelle.setAttribute('href', ficheMatiereViewUrl.replace("%C2%B5%25%24%C2%A3", fiche.fiche_matiere_slug));
        ficheMatiereTitle.appendChild(ficheMatiereLibelle);

        let parcoursTitle = document.createElement('div');
        parcoursTitle.classList.add('col-8');

        let parcoursLibelle = document.createElement('a');
        parcoursLibelle.textContent = `
            ${fiche.type_diplome_libelle ? fiche.type_diplome_libelle + " - " : ""}
            ${fiche.mention_libelle} - ${fiche.parcours_libelle} ${fiche.parcours_sigle ? '(' + fiche.parcours_sigle + ')' : ''}
        `;
        parcoursLibelle.classList.add('text-primary', 'font-weight-bold');
        parcoursLibelle.setAttribute('href', parcoursViewUrl.replace("%C2%B5%25%24%C2%A3", fiche.parcours_id));

        parcoursTitle.appendChild(parcoursLibelle);

        row.appendChild(parcoursTitle);
        row.appendChild(ficheMatiereTitle);

        rootNode.appendChild(row);
    });
}

function displayLoadingIcon(){
    let loadingIcon = document.createElement('i');
    loadingIcon.className = "fa-duotone fa-spinner spinning-icon mt-4";
    let rootNode = document.querySelector('.loading-icon')
    rootNode.appendChild(loadingIcon);
}

function hideLoadingIcon(){
    document.querySelector('.spinning-icon').remove();
}

function emptyResultList(){
    let rootNode = document.querySelector(".rootNodeForFicheMatiereList");
    while(rootNode.hasChildNodes()){
        rootNode.removeChild(rootNode.firstChild);
    }   
}


