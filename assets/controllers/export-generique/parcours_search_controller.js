import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    
    static targets = [
        'searchForm',
        'searchInput',
        'searchResult',
        'emptySearchListButton',
        'selectAllParcoursButton',
        'loadingIcon'
    ];

    static values = {
        searchUrl: String
    };

    _minLength = 4;

    connect(){
        this.searchFormTarget.addEventListener('submit', async (event)=> {
            event.preventDefault();
            await this.loadResultList();
        })
    }

    async fetchResults(searchText){
        let url = `${this.searchUrlValue}?inputText=${searchText}`;
        return await fetch(url)
            .then(response => response.json())
            .then(jsonData => jsonData)
            .catch(error => console.log(error));
    }

    async loadResultList(){
        if(this.searchInputTarget.value.length >= this._minLength){    
            this.emptyResultList();
            this.loadingIconTarget.classList.remove('d-none');
            let searchResult = await this.fetchResults(this.searchInputTarget.value);
            this.createResultList(searchResult);
            this.loadingIconTarget.classList.add('d-none');
            this.emptySearchListButtonTarget.classList.remove('d-none');
        } else {

        }
    }

    createResultList(resultArray){
        resultArray.forEach(parcours => {
            this.searchResultTarget.appendChild(
                this.createSearchResultNode(parcours.parcours_libelle)
            );
        });
    }

    createSearchResultNode(resultName){
        let row = document.createElement('div');
        row.classList.add('row', 'my-1');
        let textDiv = document.createElement('div');
        textDiv.classList.add('resultSearchNode')
        textDiv.textContent = resultName;
        row.appendChild(textDiv);

        return row;
    }

    emptyResultList(){
        while(this.searchResultTarget.hasChildNodes()){
            this.searchResultTarget.removeChild(this.searchResultTarget.firstChild);
        }
    }

    emptyResultListEvent(){
        this.emptyResultList();
        this.emptySearchListButtonTarget.classList.add('d-none');
        this.selectAllParcoursButtonTarget.classList.remove('d-none');
    }

    selectAllParcours(){
        this.emptyResultList();
        let row = document.createElement('div');
        row.classList.add('row', 'mt-5');
        let textDiv = document.createElement('div');
        textDiv.classList.add('allParcoursTextNode', 'text-center', 'text-primary');
        textDiv.textContent = "Tous les parcours ont été sélectionnés";
        row.appendChild(textDiv);
        this.selectAllParcoursButtonTarget.classList.add('d-none');
        this.emptySearchListButtonTarget.classList.remove('d-none');
        this.searchResultTarget.appendChild(row);
    }
}