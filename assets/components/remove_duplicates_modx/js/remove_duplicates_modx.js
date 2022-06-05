"use strict";

// этот код работает в современном режиме
var connector = '../assets/components/remove_duplicates_modx/connectors/connector.php';

document.addEventListener('DOMContentLoaded', function () {

    let header = document.querySelector('#modx-panel-holder');
    header.innerHTML = "<h1>Remove Duplicates MODX</h1>";

    getDuplicates();


});

function getDuplicates() {

    var dataForm = new FormData();
    dataForm.append("action", 'list');

    fetch(connector, {
        method: 'POST', body: dataForm
    })
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            getDuplicates_renderer(data);
        });
}

function getDuplicates_renderer(data) {

    var bodyPanel = document.getElementById('doubles_panel');
    bodyPanel.innerHTML = 'Loading...';
    //console.log('bodyPanel',bodyPanel,data);


    if (data.st === "error") {
        bodyPanel.innerHTML = `<div class="alert alert-danger">${data.msg}</div>`;
    } else {

        let tableContent = ``;

        let items = data.doubles;


        if (Object.keys(items).length) {


            for (const context_key of Object.keys(items)) {
                let trs = ``;

                let c_items = items[context_key];

                for (const resource of Object.keys(c_items)) {
                    let elements_d = c_items[resource];


                    trs += `<tr>
                <td><strong>${elements_d[0].pagetitle}</strong></td>
                <td><strong>${elements_d.length - 1}</strong></td>
              </tr>`

                    let inputs = ``;

                    elements_d.forEach(function (item, index, array) {


                        let checked = (index > 0) ? `checked="checked"` : '';
                        let disabled = (index === 0) ? `d-none ` : '';
                        let firstClass = (index === 0) ? `table-success` : 'table-warning';
                        let inputItem = `<div class="form-check">
                              <input ${checked}  class="form-check-input resources_items ${disabled}" name="resource" type="checkbox" value="${item.id}" id="resRem_${item.id}">
                              <label class="form-check-label" for="resRem_${item.id}">
                                <a  target="_blank" href="?a=resource/update&id=${item.id}">#${item.id} ${item.pagetitle} </a> / ${item.uri}
                              </label>
                            </div>`;

                        trs += `<tr class="${firstClass}" ><td colspan="2">${inputItem}</td></tr>`

                    });

                }


                tableContent += `<h4>Context: <span class="text-danger text-uppercase">${context_key}</span></h4>
          <table id="table_${context_key}" class="table table-bordered">
            <thead><tr><th>Ресурс</th><th>Кол-во дублей</th></tr></thead>
            <tbody>${trs}</tbody>
          </table>`;


            }
            bodyPanel.innerHTML = tableContent;
        } else {
            bodyPanel.innerHTML = `<div class="alert alert-success">Тут рыбы нет)</div>`;
        }

        renderPanelActions();

    }
}

function renderPanelActions() {
    var formPanel = document.getElementById('form_panel');

    let p_form = `<form id="form_panel_form" class="ms-auto form_panel_form" method="get" action="${connector}">
                <input type="hidden" name="action" value="work_res">
                <div class="row">
                  <div class="col-sm-9">
                    <select name="who_is" class="form-select " >
                      <option value="delete">Переместить в корзину (удалить)</option>
                      <option value="genAlias">Сгенерировать алиас</option>
                    </select>
                  </div>
                  
                  <div class="col-sm-3">
                    <button type="submit" class="btn btn-primary pull-right">OK</button>
                  </div>
                </div>
            </form>`;




    if(document.querySelector('.form_panel_form')===null){
        formPanel.innerHTML = p_form;
    }




    let formElem = document.getElementById("form_panel_form");
    formElem.addEventListener("submit", function (e) {

        let checkeds = document.querySelectorAll('.resources_items:checked');
        let listChecked = Array.from(checkeds).map(el => el.value);

        listChecked.forEach(function (item, index, array) {

            let nFomdata = new FormData(formElem);
            nFomdata.append("resource", item);
            nFomdata.append("iterate", index+1);
            nFomdata.append("iterate_off", array.length);
            fetch(connector, {
                method: 'POST', body: nFomdata
            })
                .then((response) => {
                    return response.json();
                })
                .then((data) => {
                    console.log('form', data);
                    if(data.end){
                        //window.location.reload();
                        getDuplicates();
                    }

                });
        });


        e.preventDefault();

    });

}