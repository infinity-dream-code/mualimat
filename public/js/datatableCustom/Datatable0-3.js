function debounce(func, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
    };
}

function mergeTableRows(tableSelector, columnIndex) {
    const table = $(tableSelector);
    let prevCell = null;
    let rowspan = 1;

    table.find(`tbody tr`).each(function () {
        const currentCell = $(this).find(`td:eq(${columnIndex})`);
        if (prevCell && currentCell.text() === prevCell.text()) {
            currentCell.hide();
            prevCell.attr('rowspan', ++rowspan);
        } else {
            prevCell = currentCell;
            rowspan = 1;
        }
    });
}

function formatColumnName(columnName) {
    return columnName.replace(/_/g, ' ').replace(/\b\w/g, match => match.toUpperCase());
}

function createColumnsHtml(columns) {
    return columns.map(column => `<th>${formatColumnName(column.name)}</th>`).join('');
}

function createColumns(id, columns, location) {
    const table = $(`#${id}`);
    let headerOrFooter = table.find(location);
    if (headerOrFooter.length === 0) {
        headerOrFooter = $(`<${location} class="table-light"></${location}>`);
        table.append(headerOrFooter);
    }
    headerOrFooter.empty();
    const row = $('<tr class="text-start fw-bold fs-7 text-uppercase gs-0">').html(createColumnsHtml(columns));
    headerOrFooter.html(row);
}


function dataTableCreate(id, dataUrl, dataColumns, formId = null, search = true, select = false) {
    let idTable = $(`#${id}`);
    let searchPanel = [];
    idTable.DataTable({
        autoWidth: false,
        responsive: false,
        scrollX: true,
        columns: dataColumns,
        fixedHeader: false,
        searching: search,
        select: select ? {
            style: 'multi'
        } : false,
        columnDefs: [
            {
                targets: 0,
                searchable: false,
                orderable: false,
                className: select? '': ' table_dt_no',
                checkboxes: select ? {
                    selectRow: true,
                    selectAllRender: '<input type="checkbox" class="form-check-input select-all">'
                } : false,
            }
        ],
        ajax: {
            url: dataUrl,
            type: "GET",
            data: function (d) {
                if (formId) {
                    let transformedData = formId ? $(`#${formId}`).serializeArray().reduce((acc, {name, value}) => {
                        if (acc[name]) {
                            if (!Array.isArray(acc[name])) {
                                acc[name] = [acc[name]];
                            }
                            if (Array.isArray(value)) {
                                acc[name] = acc[name].concat(value);
                            } else {
                                acc[name].push(value);
                            }
                        } else {
                            acc[name] = value;
                        }
                        return acc;
                    }, {}) : {};
                    return $.extend({}, d, transformedData);
                }
            }, error: function () {
                errorAlert('Ada masalah saat mengambil data dari server, Silahkan muat ulang halaman')
            }
        },
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.0.6/i18n/id.json',
        },
        processing: true,
        serverSide: true,
        deferRender: true,
        preDrawCallback: function (settings) {
            if (formId) {
                let submitButton = $(`#${formId} input[type="submit"], #${formId} button[type="submit"]`);
                let resetButton = $(`#${formId} input[type="reset"], #${formId} button[type="reset"]`);

                if (submitButton.length !== 0) {
                    submitButton.prop('disabled', true);
                    submitButton.html(`<span class="spinner-border me-2" role="status" aria-hidden="true"></span>Cari`);
                }
                if (resetButton.length !== 0) {
                    resetButton.prop('disabled', true);
                    resetButton.html(`<span class="spinner-border me-2" role="status" aria-hidden="true"></span>Cari`);
                }
            }
        },
        drawCallback: function (settings) {
            let labelNo = $(idTable.DataTable().table().header()).find('th').eq(0);
            labelNo && labelNo.removeClass('sorting_asc');

            if (formId) {
                let submitButton = $(`#${formId} input[type="submit"], #${formId} button[type="submit"]`);
                let resetButton = $(`#${formId} input[type="reset"], #${formId} button[type="reset"]`);
                if (submitButton.length !== 0) {
                    submitButton.html(`<span class="ri-search-line me-2"></span>Cari`);
                    submitButton.prop('disabled', false);
                }
                if (resetButton.length !== 0) {
                    resetButton.html(`<span class="ri-reset-left-line me-2"></span>Reset`);
                    resetButton.prop('disabled', false);
                }
            }
        },

        initComplete: function (data) {
            //// for fixed header only -BIKIN BUBRAH THEAD-
            // if (window.Helpers.isNavbarFixed()) {
            //     let navHeight = $('#layout-navbar').outerHeight();
            //     new $.fn.dataTable.FixedHeader($(idTable).dataTable()).headerOffset(navHeight);
            // } else {
            //     new $.fn.dataTable.FixedHeader($(idTable).dataTable());
            // }

            setTimeout(function () {
                let labelNo = $(idTable.DataTable().table().header()).find('th').eq(0);
                labelNo && labelNo.removeClass('sorting_asc');

                searchPanel[id] = $(`#${id}_filter input`);
                if (searchPanel[id]) {
                    searchPanel[id].unbind();
                    searchPanel[id].bind().on('keyup', debounce(function () {
                        idTable.DataTable().search(this.value).draw();
                    }, 500));
                }
            }, 0)
        },
        error: function (xhr, error, code) {
            errorAlert('Data tidak dapat dimuat')
        }
    })


}

function dataReload(id = null) {
    id && $(`#${id}`).DataTable().ajax.reload();
}

function dataReFilter(id = null, formId = null) {
    id && $(`#${id}`).DataTable().draw();
    // if (id) {
    //     const tableId = $(`#${id}`);
    //     tableId.DataTable().draw();
    // }
}

function getDT(id, columnUrl, dataUrl, dataColumns, formId, thead, search = true, select = false) {
    $.ajax({
        url: columnUrl,
        success: function (data) {
            $.each(data, function (index, column) {
                let renderFunc = '';
                if (column.columnType) {
                    switch (column.columnType.toLowerCase()) {
                        case 'suffix':
                            renderFunc = function (data, type, row) {
                                if (type === 'display' || type === 'filter') {
                                    return data + ' ' + column.suffix;
                                }
                                return data;
                            };
                            break;
                        case 'prefix':
                            renderFunc = function (data, type, row) {
                                if (type === 'display' || type === 'filter') {
                                    return column.prefix + ' ' + data;
                                }
                                return data;
                            };
                            break;
                        case 'currency':
                            renderFunc = function (data, type, row) {
                                if (data === null || data === 0) {
                                    return 'Rp. 0';
                                }
                                return $.fn.dataTable.render.number('.', ',', 0, 'Rp. ').display(data);
                            };
                            break;
                        case 'date':
                        case 'dateformat':
                            renderFunc = function (data, type, row) {
                                if (type === 'display' || type === 'filter') {
                                    let date = new Date(data);
                                    let options = {
                                        weekday: 'long',
                                        day: 'numeric',
                                        month: 'long',
                                        year: 'numeric'
                                    };
                                    return date.toLocaleDateString('id-ID', options);
                                }
                                return data;
                            };
                            break;
                        case 'timestamp':
                            renderFunc = function (data, type, row) {
                                if (type === 'display' || type === 'filter') {
                                    let date = new Date(data);
                                    let options = {
                                        weekday: 'long',
                                        day: 'numeric',
                                        month: 'long',
                                        year: 'numeric',
                                        hour: 'numeric',
                                        minute: 'numeric'
                                    };
                                    return date.toLocaleDateString('id-ID', options);
                                }
                                return data;
                            };
                            break;
                        case 'button':
                            renderFunc = function (data, type, row) {
                                if (data && type === 'display' || type === 'filter') {
                                    let btnStyle = column.buttonClass ?? 'btn';
                                    let iconStyle = column.buttonIcon ? `<i class="${column.buttonIcon}"></i>` : column.buttonIconSVG ? column.buttonIconSVG : '';
                                    let buttonText = column.noCaption ? '' : column.buttonText;
                                    let buttonTitle = column.buttonText ?? '';
                                    let rowDataJson = JSON.stringify(row).replace(/'/g, "&#39;") .replace(/"/g, "&quot;");
                                    switch (column.button) {
                                        case 'modal':
                                            return `
                                            <button type="button" class="${btnStyle}" title="${buttonTitle}" data-bs-toggle="modal" data-bs-target="${column.buttonLink}" data-val='${rowDataJson}'>
                                                ${iconStyle}
                                                ${buttonText}
                                            </button>
                                            `;
                                        case 'link':
                                            let link = column.buttonLink ? column.buttonLink.replace(':id', row.item_id) : '#';
                                            return `<a type="button" class="${btnStyle}" href="${link}" title="${buttonTitle}">${iconStyle}${buttonText}</a>`;
                                        case 'action':
                                            return `<button type="button" class="${btnStyle}" title="${buttonTitle}" data-val='${rowDataJson}'>${iconStyle}${buttonText}</button>`;
                                        default :
                                            return '';
                                    }
                                } else {
                                    return '';
                                }
                            }
                            break;
                        case 'boolean':
                            renderFunc = function (data, type, row) {
                                let trueVal = column.trueVal ?? 'benar';
                                let falseVal = column.falseVal ?? 'Salah';
                                if (column.booleanCheck) {
                                    trueVal = '<i class="ri-check-line"></i>';
                                    falseVal = '<i class="ri-close-line"></i>';
                                }
                                if (data === "1" || data === 1 || data === true) {
                                    return `<span class="badge px-2 rounded-pill bg-label-success">${trueVal}</span>`
                                } else {
                                    return `<span class="badge px-2 rounded-pill bg-label-danger">${falseVal}</span>`
                                }
                            }
                            break;
                        case 'importstatus':
                            renderFunc = function (data, type, row) {
                                let saveVal = column.saveVal ?? 'Dapat Disimpan';
                                let updateVal = column.updateVal ?? 'Update';
                                let falseVal = column.falseVal ?? 'Tidak Dapat Disimpan';
                                if (data === "1" || data === 1 || data === true) {
                                    return `<span class="badge px-2 rounded-pill bg-label-success">${saveVal}</span>`;
                                } else if (data === "2" || data === 2) {
                                    return `<span class="badge px-2 rounded-pill bg-label-warning">${updateVal}</span>`;
                                } else if (data === "0" || data === 0 || data === false) {
                                    return `<span class="badge px-2 rounded-pill bg-label-danger">${falseVal}</span>`;
                                }
                            }
                            break;
                        case 'checkbox':
                            renderFunc = function (data, type, row) {
                                let name = column.selectName ? column.selectName : 'checkbox';
                                return `<input type="checkbox" class="dt-checkboxes form-check-input" name="${column.selectName ? column.selectName : 'checkbox'}[]" value="${data}">`;
                            }
                            break;
                        case 'custom_code_tagihan':
                            renderFunc = function (data, type, row) {
                                const descriptions = {
                                    '1140000': 'Manual Cash',
                                    '1140001': 'Manual BMI',
                                    '1140002': 'Manual SALDO',
                                    '1140003': 'Transfer Bank Lain',
                                    '1140004': 'INFAQ',
                                    '1200001': 'Loket Manual - Beasiswa',
                                    '1200002': 'Loket Manual - Potongan',
                                    '1': 'Transfer Online',
                                    '4': 'Transfer Online',
                                    null: '',
                                    '': ''
                                };
                                return descriptions[data] || data;
                            }
                            break;
                    }
                } else {
                    renderFunc = function (data, type, row) {
                        if (!data) {
                            return '<div style="text-align: center;">-</div>';
                        }
                        return data;
                    }
                }

                dataColumns.push({
                    data: column.data,
                    name: column.name,
                    searchable: column.searchable ?? false,
                    orderable: column.orderable ?? false,
                    render: renderFunc ?? false,
                    className: column.className ?? false,
                    search: false,
                })
            })
            if (thead) {
                createColumns(id, dataColumns, 'thead');
                // createColumns(id, dataColumns, 'tfoot');
            }
            dataTableCreate(id, dataUrl, dataColumns, formId, search, select)
        }
    });
}
