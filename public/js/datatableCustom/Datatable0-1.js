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
        headerOrFooter = $(`<${location}></${location}>`);
        table.append(headerOrFooter);
    }
    headerOrFooter.empty();
    const row = $('<tr class="text-start fw-bold fs-7 text-uppercase gs-0">').html(createColumnsHtml(columns));
    headerOrFooter.html(row);
}

function dataTableCreate(id, dataUrl, dataColumns, formId) {
    let idTable = $(`#${id}`);
    // let transformedData = formId ? $(`#${formId}`).serialize() : null;
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
    idTable.DataTable({
        autoWidth: false,
        responsive: false,
        columns: dataColumns,
        searchDelay: 1000,
        "columnDefs": [
            {
                "targets": 0,
                "width": "25px",
                "maxWidth": "25px"
            }
        ],
        ajax: {
            url: dataUrl,
            type: "GET",
            data: transformedData,
            error: function () {
                errorAlert('Ada masalah saat mengambil data dari server. Silahkan muat ulang halaman')
            }
        },
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.0.6/i18n/id.json',
        },
        processing: true,
        serverSide: true,
        initComplete: function (data) {
            $(`[aria-label='No']`).removeClass('sorting_asc');
            // $(`${idTable.selector} tbody td:first-child`).removeClass('');
            // $(`${idTable} thead`).addClass('sticky-element');
        },
        error: function (xhr, error, code) {
            errorAlert('Data tidak dapat dimuat')
        }
    })
}

function dataReload(id) {
    $(`#${id}`).DataTable().ajax.reload();
}

function dataReFilter(id, dataUrl, dataColumns, formId) {
    $(`#${id}`).DataTable().destroy();
    dataTableCreate(id, dataUrl, dataColumns, formId);
}

function getDT(id, columnUrl, dataUrl, dataColumns, formId, thead) {
    $.ajax({
        url: columnUrl,
        success: function (data) {
            $.each(data, function (index, column) {
                let renderFunc = '';
                if (column.columnType) {
                    switch (column.columnType) {
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
                        case 'dateFormat':
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
                        case 'timeStamp':
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
                                    switch (column.button) {
                                        case 'modal':
                                            let rowDataJson = JSON.stringify(row);
                                            return `
                        <button class="${btnStyle}" title="${column.buttonText}" data-bs-toggle="modal" data-bs-target="${column.buttonLink}" data-val='${rowDataJson}'>
                            ${iconStyle}
                            ${buttonText}
                        </button>
                        `;
                                        case 'link':
                                            let link = column.buttonLink ? column.buttonLink.replace(':id', row.item_id) : '#';
                                            return `<button class="${btnStyle}" href="${link}" title="${buttonText}">${iconStyle}${buttonText}</button>`;
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
                        case 'custom_code_tagihan':
                            renderFunc = function (data,type,row){
                                const descriptions = {
                                    '1140000': 'Manual Cash',
                                    '1140001': 'Manual BMI',
                                    '1140002': 'Manual SALDO',
                                    '1140003': 'Transfer Bank Lain',
                                    '1140004': 'INFAQ',
                                    '1200001': 'Loket Manual - Beasiswa',
                                    '1200002': 'Loket Manual - Potongan'
                                };
                                return descriptions[data] || '-';
                            }
                            break;
                    }
                }

                dataColumns.push({
                    data: column.data,
                    name: column.name,
                    searchable: column.searchable ?? false,
                    orderable: column.orderable ?? false,
                    render: renderFunc ?? false,
                    className: column.className ?? false,
                })
            })
            if (thead) {
                createColumns(id, dataColumns, 'thead');
            }
            dataTableCreate(id, dataUrl, dataColumns, formId)
        }
    });
}
