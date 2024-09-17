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
    let buttonGoToPage = document.querySelector('.button-go-to-page');

    let inputNumeroPage = document.querySelector("input[name='inputNumeroPage']");

    if(totalNumber > 1){
        // Affichage du résultat pour la page 1
        await displayResult(fetchUrl, currentPage, keyword, parcoursViewUrl, ficheMatiereViewUrl, totalPageNumber);
        /**
         * Navigation vers la page souhaitée
         */
        buttonPageLeft.addEventListener('click', async e => {
            if(currentPage > 1){
                currentPage -= 1;
                await displayResult(fetchUrl, currentPage, keyword, parcoursViewUrl, ficheMatiereViewUrl, totalPageNumber);
            }
        })
    
        buttonPageRight.addEventListener('click', async e => {
            if(currentPage < totalPageNumber){
                currentPage += 1;
                await displayResult(fetchUrl, currentPage, keyword, parcoursViewUrl, ficheMatiereViewUrl, totalPageNumber);
            }
        });
        
        buttonGoToPage.addEventListener('click', async e => {
            let value = Number(inputNumeroPage.value);
            if(Number.isInteger(value) === false || value < 1){
                value = 1;
            }
            if(value > totalPageNumber){
                value = totalPageNumber;
            }
            currentPage = value;
            inputNumeroPage.value = value;
            await displayResult(fetchUrl, currentPage, keyword, parcoursViewUrl, ficheMatiereViewUrl, totalPageNumber);
        })
        /*************************************/
    }

});


async function displayResult(fetchUrl, pageNumber, keyword, parcoursViewUrl, ficheMatiereViewUrl, totalPageNumber){
    emptyResultList();
    displayLoadingIcon();
    let url = configureFetchUrl(fetchUrl, pageNumber, keyword);
    let result = await fetchResultPage(url);
    if(result){
        hideLoadingIcon();
        updateDomWithResult(result, parcoursViewUrl, ficheMatiereViewUrl, keyword);
        updatePageLabel(pageNumber, totalPageNumber);
    }
}

function updatePageLabel(pageNumber, totalPageNumber){
    let label = document.querySelector('.numero-page');
    label.textContent = `Page ${pageNumber} / ${totalPageNumber}`;
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

function updateDomWithResult(jsonResult, parcoursViewUrl, ficheMatiereViewUrl, keyword){
    let rootNode = document.querySelector(".rootNodeForFicheMatiereList");  

    jsonResult.forEach(fiche => {
        let row = document.createElement('div');
        row.classList.add("row", "my-3", "py-3", "px-2", "border", "border-primary", "rounded");

        let ficheMatiereTitle = document.createElement('div');
        ficheMatiereTitle.classList.add('col-4');
        
        let ficheMatiereTitleDiv = document.createElement('div');
        ficheMatiereTitleDiv.classList.add('col-12');

        let ficheMatiereLibelle = document.createElement('a');
        ficheMatiereLibelle.textContent = fiche.fiche_matiere_libelle;
        ficheMatiereLibelle.setAttribute('href', ficheMatiereViewUrl.replace("%C2%B5%25%24%C2%A3", fiche.fiche_matiere_slug));
        ficheMatiereTitleDiv.appendChild(ficheMatiereLibelle);

        let ficheMatierePillDiv = document.createElement('div');
        ficheMatierePillDiv.classList.add('col-12', 'mt-3');

        ficheMatiereTitle.appendChild(ficheMatiereTitleDiv);
        ficheMatiereTitle.appendChild(ficheMatierePillDiv);

        if(isStringContainingText(fiche.fiche_matiere_objectifs, keyword)){
            let objectifsPill = displayBadge('Objectifs', 'warning');
            ficheMatierePillDiv.appendChild(objectifsPill);
        }
        if(isStringContainingText(fiche.fiche_matiere_description, keyword)){
            let descriptionPill = displayBadge('Description', 'info');
            ficheMatierePillDiv.appendChild(descriptionPill);
        }

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
    hideLoadingIcon();
    let loadingIcon = document.createElement('i');
    loadingIcon.className = "fa-duotone fa-spinner spinning-icon mt-4";
    let rootNode = document.querySelector('.loading-icon')
    rootNode.appendChild(loadingIcon);
}

function hideLoadingIcon(){
    if(document.querySelector('.spinning-icon')){
        document.querySelector('.spinning-icon').remove();
    }
}

function emptyResultList(){
    let rootNode = document.querySelector(".rootNodeForFicheMatiereList");
    while(rootNode.hasChildNodes()){
        rootNode.removeChild(rootNode.firstChild);
    }   
}

function isStringContainingText(string, needle){
    if(string){
        return string.toUpperCase().includes(
            needle.toUpperCase()
        );
    }

    return false;
}

function displayBadge(text, color){
    let pill = document.createElement('span');
    pill.classList.add('badge', 'rounded-pill', `text-bg-${color}`);
    pill.textContent = text;

    return pill;
}


