document.addEventListener('DOMContentLoaded', (e) => {
    let currentPage = 1;

    let dataObject = document.querySelector('#dataFicheMatiereSearch');

    let totalNumber = Number(dataObject.getAttribute('data-nb-fiches-total'));
    let totalPageNumber = Math.floor(totalNumber / 30);
    let keyword = dataObject.getAttribute('data-keyword');
    let fetchUrl = dataObject.getAttribute('data-fetch-url');

    console.log(fetchUrl);

    let buttonPageRight = document.querySelector('i.button-page-right');
    let buttonPageLeft = document.querySelector('i.button-page-left');

    buttonPageLeft.addEventListener('click', async e => {
        if(currentPage > 1){
            currentPage -= 1;
            let url = configureFetchUrl(fetchUrl, currentPage, keyword);
            let result = await fetchResultPage(url);
            updateDomWithResult(result);
            updatePageLabel(currentPage);
        }
    })

    buttonPageRight.addEventListener('click', async e => {
        if(currentPage < totalPageNumber){
            currentPage += 1;
            let url = configureFetchUrl(fetchUrl, currentPage, keyword);
            let result = await fetchResultPage(url);
            updateDomWithResult(result);
            updatePageLabel(currentPage);
        }
    });
});

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

function updateDomWithResult(jsonResult){
    let rootNode = document.querySelector(".rootNodeForFicheMatiereList");

    while(rootNode.hasChildNodes()){
        rootNode.removeChild(rootNode.firstChild);
    }    

    jsonResult.forEach(fiche => {
        let row = document.createElement('div');
        row.classList.add("row", "my-3", "py-2", "px-2", "border", "border-primary", "rounded");

        let title = document.createElement('div');
        title.classList.add('col-8');
        title.textContent = fiche.fiche_matiere_libelle;

        let parcoursTitle = document.createElement('div');
        parcoursTitle.classList.add('col-4');
        parcoursTitle.textContent = fiche.parcours_libelle;

        row.appendChild(title);
        row.appendChild(parcoursTitle);

        rootNode.appendChild(row);
    });
}


