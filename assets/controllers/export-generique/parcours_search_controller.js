import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    
    static targets = [
        'searchForm',
        'searchInput',
        'searchResult',
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
            let searchResult = await this.fetchResults(this.searchInputTarget.value);
            this.emptyResultList();
            this.createResultList(searchResult);
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
}