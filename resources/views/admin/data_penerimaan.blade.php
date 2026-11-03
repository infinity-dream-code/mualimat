@extends('layouts.admin_new')
@section('title',$dataTitle??$mainTitle??$title??'')
@section('style')
    <link rel="stylesheet" href="{{asset('main/libs/select2/select2.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/datatables-bs5/datatables.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.css')}}">
    <style>
        .select2-container--default .select2-results__option[aria-disabled=true] {
            display: none;
        }
    </style>
@endsection
@section('content')
    <h3 class="page-heading d-flex text-gray-900 fw-bold flex-column justify-content-center my-0">
        @if(isset($dataTitle) && isset($mainTitle) && $mainTitle != $dataTitle)
            {{$mainTitle .' - '.$dataTitle}}
        @else
            {{$mainTitle??$title??''}}
        @endif
    </h3>
    <ul class="breadcrumb breadcrumb-style2">
        <li class="breadcrumb-item">
            <a href="{{route('admin.index')}}" class="text-hover-primary">Beranda</a>
        </li>
        @if(isset($title))
            <li class="breadcrumb-item">
                {{$title}}
            </li>
        @endif
        @if(isset($mainTitle))
            <li class="breadcrumb-item">
                {{$mainTitle}}
            </li>
        @endif
        @if(isset($dataTitle) && isset($mainTitle) && $mainTitle != $dataTitle)
            <li class="breadcrumb-item active">
                {{$dataTitle}}
            </li>
        @endif
    </ul>

    <div class="card">
        <div class="card-header">
            <div class="row mb-3">
                <h5 class="mb-0 me-2">{{($dataTitle??$mainTitle??$title)}}</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="row px-5 mb-2">
                <ul class="list-group list-group-timeline">
                    <li class="list-group-item list-group-timeline-danger">
                        <strong>Pastikan telah mengisi Tanggal Transaksi!</strong>
                    </li>
                    <li class="list-group-item list-group-timeline-danger">
                        <strong>Pastikan browser anda tidak memblokir <i>POP-UP</i>!</strong>
                    </li>
                </ul>
            </div>
            <form id="filterForm">
                <fieldset class="form-fieldset">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-5">
                                <label class="form-label" for="dari-tanggal">Tanggal Transaksi <span
                                        class="text-warning">*</span>(tanggal-bulan-tahun - tanggal-bulan-tahun)</label>
                                <input type="text" id="tanggal-transaksi" name="filter[tanggal-transaksi]"
                                       placeholder="tanggal/bulan/tahun"
                                       class="form-control" autocomplete="false" inputmode="numeric"/>
                            </div>
                            <div class="mb-5">
                                <label class="form-label" for="tahun_akademik">
                                    Tahun Akademik
                                </label>
                                <select class="form-select" id="tahun_akademik"
                                        name="filter[tahun_akademik]"
                                        data-control="select2"
                                        data-placeholder="Pilih Tahun Akademik">
                                    <option value="all">Semua</option>
                                    @isset($thn_aka)
                                        @foreach($thn_aka as $item)
                                            <option
                                                value="{{$item->thn_aka}}">{{$item->thn_aka}}</option>
                                        @endforeach
                                    @else
                                        <option>data kosong</option>
                                    @endisset
                                </select>
                            </div>
                            <div class="mb-5">
                                <label class="form-label" for="post">
                                    Nama Tagihan
                                </label>
                                <select class="form-select" id="post"
                                        name="filter[post][]"
                                        data-control="select2"
                                        data-placeholder="Pilih Tagihan"
                                        multiple="multiple">
                                    <option value="all">Semua</option>
                                    @isset($post)
                                        @foreach($post as $item)
                                            <option
                                                value="{{$item->tagihan}}">{{$item->tagihan}}</option>
                                        @endforeach
                                    @else
                                        <option>data kosong</option>
                                    @endisset
                                </select>
                            </div>
                            <div class="mb-5">
                                <label class="form-label" for="metode_bayar">
                                    Metode Pembayaran
                                </label>
                                <select class="form-select" id="metode_bayar"
                                        name="filter[metode_bayar]"
                                        data-control="select2"
                                        data-placeholder="Pilih Tagihan">
                                    @isset($metode_bayar)
                                        <option value="all">Semua</option>
                                        @foreach($metode_bayar as $key => $item)
                                            <option
                                                value="{{$key}}" data-value="{{$item}}">{{$item}}</option>
                                        @endforeach
                                    @else
                                        <option>data kosong</option>
                                    @endisset
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="col mb-5">
                                <label class="form-label" for="filter[angkatan]]">
                                    Angkatan Siswa
                                </label>
                                <select class="form-select" id="filter[angkatan]"
                                        name="filter[angkatan]"
                                        data-control="select2"
                                        data-placeholder="Pilih Angkatan Siswa">
                                    <option value="all">Semua</option>
                                    @isset($thn_aka)
                                        @foreach($thn_aka as $item)
                                            <option
                                                value="{{$item->thn_aka}}">{{$item->thn_aka}}</option>
                                        @endforeach
                                    @else
                                        <option>data kosong</option>
                                    @endisset
                                </select>
                            </div>
                            <div class="col mb-5">
                                <label class="form-label" for="filter[unit]">
                                    Tingkat
                                </label>
                                <select class="form-select" id="filter[unit]" name="filter[unit]"
                                        data-control="select2" data-placeholder="Pilih unit">
                                    <option value="all">Semua</option>
                                    @isset($unit)
                                        @foreach($unit as $item)
                                            <option
                                                value="{{$item->CODE01}}" data-group="{{$item->DESC01}}">{{$item->DESC01}}</option>
                                        @endforeach
                                    @else
                                        <option>data kosong</option>
                                    @endisset
                                </select>
                            </div>
                            <div class="col mb-5">
                                <label class="form-label" for="filter[kelas]">
                                    Kelas
                                </label>
                                <select class="form-select" id="filter[kelas]" name="filter[kelas]"
                                        data-control="select2" data-placeholder="Pilih Kelas">
                                    <option value="all">Semua</option>
                                    @isset($kelas)
                                        @foreach($kelas as $item)
                                            <option
                                                value="{{$item->unit}}~{{$item->jenjang}}~{{$item->kelas}}" data-group="{{$item->unit}}">
                                                {{$item->unit}} - {{$item->jenjang}} {{$item->kelas}}
                                            </option>
                                        @endforeach
                                    @else
                                        <option>data kosong</option>
                                    @endisset
                                </select>
                            </div>
                            <div class="col mb-5">
                                <label class="form-label" for="filter[siswa]">
                                    Siswa
                                </label>
                                <input class="form-control" id="filter[siswa]" name="filter[siswa]"
                                       placeholder="Masukkan NIS/NAMA Siswa" data-placeholder="Pilih siswa">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="d-flex justify-content-center flex-column flex-md-row justify-content-md-end gap-4">
                            <button type="button" class="btn btn-facebook btn-print-rekap" id="cetak-rekap">
                                <span class="ri-file-text-line me-2"></span>
                                Cetak Rekap
                            </button>
                            <button type="reset" class="btn btn-secondary" disabled>
                                <span class="ri-reset-left-line me-2"></span>
                                Reset
                            </button>
                            <button type="submit" class="btn btn-primary" disabled>
                                <span class="ri-search-line me-2"></span>
                                Cari
                            </button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
        <div class="card-datatable table-responsive text-nowrap">
            <table class="table table-sm table-bordered table-hover"
                   id="main_table">
                <thead class="table-light">

                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{asset('main/libs/select2/select2.js')}}"></script>
    <script src="{{asset('main/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
    <script src="{{asset('js/datatableCustom/Datatable-0-4.min.js')}}?v=20261103-nodup"></script>
    <script src="{{asset('main/libs/moment/moment.js')}}"></script>
    <script src="{{asset('main/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js')}}"></script>

    <script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>

    <script type="text/javascript" defer>
        const select2 = $(`[data-control='select2']`);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let dtOptions = {
            tableId: 'main_table',
            formId: 'filterForm',
            columnUrl: '{{($columnsUrl??null)}}',
            dataUrl: '{{($datasUrl??null)}}',
            dataColumns: [],
            thead: true,
            tfoot: true,
            paging: true,
            searching: true,
            fixedHeader: false,
            // select: true,
            cache: true,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
            buttons: ['copy', 'excel', 'pdf'],
        };

        document.addEventListener("DOMContentLoaded", function () {
            if (dtOptions.dataUrl && dtOptions.columnUrl) {
                getDT(dtOptions);
                if (dtOptions.formId) {
                    let filterForm = $(`#${dtOptions.formId}`);
                    filterForm.on('submit', function (e) {
                        e.preventDefault();
                        dataReFilter(dtOptions.tableId);
                    });
                    filterForm.on('reset', function (e) {
                        setTimeout(function () {
                            dataReFilter(dtOptions.tableId);
                            const select2InForm = select2.filter(`#${dtOptions.formId} [data-control='select2']`);
                            if (select2InForm.length) {
                                select2InForm.each(function () {
                                    let $this = $(this);
                                    $this.trigger('change');
                                });
                            }
                        }, 0)
                    });
                }
            }

            if (select2.length) {
                select2.each(function () {
                    let $this = $(this);
                    // select2Focus($this);
                    $this.wrap('<div class="position-relative"></div>').select2({
                        placeholder: 'Select value',
                        dropdownParent: $this.parent()
                    });
                });
            }

            $("[name='filter[unit]']").on('change', function () {
                const selectedGroup = $(this).find(':selected').data('group');
                if(!selectedGroup) return;
                const $kelasSelect = $("[name='filter[kelas]']");
                if($(this).val() === 'all'){
                    $kelasSelect.find('option').each(function () {
                        $(this).prop('disabled', false);
                    });
                }else{
                    $kelasSelect.find('option').each(function () {
                        const group = $(this).data('group');
                        if (!group) return;
                        $(this).prop('disabled', group.toLowerCase() !== selectedGroup.toLowerCase());
                    });
                }
                $kelasSelect.val('all').trigger('change.select2');
            });

            const $postInput = $('#post');
            $postInput.on('select2:select', function(e) {
                if (e.params.data.id === 'all') {
                    $('#post option').prop('selected', true);
                    $postInput.trigger('change');
                }
            });

            $postInput.on('select2:unselect', function (e) {
                if (e.params.data.id === 'all') {
                    let selected = $postInput.val() || [];
                    if (selected.length > 0) {
                        $postInput.val([selected[0]]).trigger('change');
                    } else {
                        $postInput.val(null).trigger('change');
                    }
                }
            });

            $postInput.on('change', function () {
                let selected = $postInput.val();
                if (!selected || selected.length === 0) {
                    let fallbackOption = $('#post option:not([value="all"])').first().val();
                    console.log(fallbackOption)
                    if (fallbackOption) {
                        $postInput.val([fallbackOption]).trigger('change');
                    }
                }
            });

            let startOfMonth = moment().startOf('month');
            let today = moment();
            let date = $('#tanggal-transaksi');
            date.daterangepicker({
                startDate: startOfMonth,
                endDate: today,
                autoUpdateInput: false,
                todayHighlight: true,
                autoclose: true,
                locale: {
                    format: 'DD-MM-YYYY',
                    separator: " - ",
                    applyLabel: "Terapkan",
                    cancelLabel: "Batal",
                    fromLabel: "Dari",
                    toLabel: "Ke",
                    customRangeLabel: "Kustom",
                    daysOfWeek: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
                    monthNames: ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"],
                    firstDay: 0,
                }
            }, function (start, end) {
                let duration = end.diff(start, 'days');
                if (duration > 730) {
                    warningAlert("Maksimal 2 tahun (730 hari).");
                    date.data('daterangepicker').setStartDate(start);
                    date.data('daterangepicker').setEndDate(start.clone().add(730, 'days'));
                }
            });

            date.on('apply.daterangepicker hide.daterangepicker', function (ev, picker) {
                if (picker.startDate && picker.endDate) {
                    $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
                }
            });

            date.on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });

            // default tampil awal bulan - hari ini, bisa dikosongkan via Batal
            date.val(startOfMonth.format('DD-MM-YYYY') + ' - ' + today.format('DD-MM-YYYY'));

            function normalizeTagihanList(tagihan) {
                if (Array.isArray(tagihan)) {
                    return tagihan.map(String).filter(Boolean);
                }
                if (tagihan && typeof tagihan === 'object') {
                    return Object.values(tagihan).map(String).filter(Boolean);
                }
                return [];
            }

            function generateTableRow(data, tagihan) {
                const tags = normalizeTagihanList(tagihan);
                return data.map(s => {
                    const row = {
                        "NIS": s.nocust ?? s.NIS ?? '-',
                        "Nama": s.nmcust ?? s.Nama ?? '-',
                    };

                    let total = 0;
                    tags.forEach(function (name) {
                        const val = Number(s[name] ?? 0);
                        row[name] = val;
                        total += val;
                    });
                    row['TOTAL'] = total;
                    return row;
                });
            }

            function parseDDMMYYYY(dateStr) {
                if (!dateStr) return null;
                const parts = String(dateStr).trim().split(/[\/\-]/);
                if (parts.length < 3) return null;
                const [dd, mm, yyyy] = parts;
                const d = new Date(Number(yyyy), Number(mm) - 1, Number(dd), 12, 0, 0);
                return Number.isNaN(d.getTime()) ? null : d;
            }

            function downloadBlob(blob, filename) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                a.style.display = 'none';
                document.body.appendChild(a);
                a.click();
                setTimeout(function () {
                    URL.revokeObjectURL(url);
                    a.remove();
                }, 1500);
            }

            async function exportExcel(groupedData, params, tagihans = []) {
                const rows = groupedData;
                if (!rows.length) {
                    throw new Error('Data Tagihan Kosong');
                }
                if (typeof ExcelJS === 'undefined') {
                    throw new Error('Library ExcelJS gagal dimuat. Periksa koneksi internet / CDN.');
                }

                const invalidValues = [null, '', 'undefined', 'all'];
                let kelasVal = params.get('filter[kelas]') ?? null;
                if (invalidValues.includes(kelasVal)) kelasVal = 'Semua';

                let thnAkaVal = params.get('filter[tahun_akademik]') ?? null;
                if (invalidValues.includes(thnAkaVal)) thnAkaVal = 'Semua';

                let angkatanVal = params.get('filter[angkatan]') ?? null;
                if (invalidValues.includes(angkatanVal)) angkatanVal = 'Semua';

                let periodeVal = params.get('filter[periode]') ?? null;
                if (invalidValues.includes(periodeVal)) periodeVal = 'Semua';

                let metodeBayarVal = params.get('filter[metode_bayar]') ?? null;
                if (invalidValues.includes(metodeBayarVal)) {
                    metodeBayarVal = 'Semua';
                } else {
                    const option = document.querySelector(`#metode_bayar option[value="${CSS.escape(String(metodeBayarVal))}"]`);
                    metodeBayarVal = option?.textContent?.trim() || metodeBayarVal;
                }

                let tanggalRaw = String(params.get('filter[tanggal-transaksi]') ?? '');
                // Pisah range saja (" - " / " ~ "), jangan pecah tanggal DD-MM-YYYY
                let tanggalTransaksi = tanggalRaw.split(/\s+-\s+|\s+~\s+|\s+\/\s+/).map(s => s.trim()).filter(Boolean);

                const tags = normalizeTagihanList(tagihans);
                const wbTitle = "REKAP DATA PENERIMAAN";
                const wb = new ExcelJS.Workbook();
                const ws = wb.addWorksheet(wbTitle.substring(0, 31));

                const header = Object.keys(rows[0]);

                ws.insertRow(1, [wbTitle]);
                ws.insertRow(2, ["Unit, Kelas", String(kelasVal).replace(/~/g, " - ")]);
                ws.insertRow(3, ["Angkatan", angkatanVal]);
                ws.insertRow(4, ["Tahun Akademik", thnAkaVal]);
                ws.insertRow(5, ["Periode", periodeVal]);
                ws.insertRow(6, ["Metode Pembayaran", metodeBayarVal]);
                ws.insertRow(7, ["Dari", parseDDMMYYYY(tanggalTransaksi[0])]);
                ws.insertRow(8, ["Hingga", parseDDMMYYYY(tanggalTransaksi[1])]);

                [7, 8].forEach(rowNumber => {
                    const cell = ws.getRow(rowNumber).getCell(2);
                    if (cell.value instanceof Date) {
                        cell.numFmt = "[$-id-ID]dddd, dd mmmm yyyy";
                    }
                });

                [1, 2, 3, 4, 5, 6, 7, 8].forEach(rowNumber => {
                    const row = ws.getRow(rowNumber);
                    row.eachCell({includeEmpty: true}, cell => {
                        cell.font = {bold: true};
                    });
                });

                ws.insertRow(9, []);
                const headerRowNumber = 10;
                ws.getColumn(1).width = 18;
                ws.getColumn(2).width = 32;

                const headerRow = ws.insertRow(headerRowNumber, header);
                headerRow.eachCell({includeEmpty: true}, (cell, colNumber) => {
                    cell.font = {bold: true};
                    cell.alignment = {horizontal: "center", vertical: "middle"};
                    cell.border = fullBorder();
                    if (colNumber <= 2) return;
                    const headerText = String(cell.value || "");
                    ws.getColumn(colNumber).width = Math.max(12, Math.min(28, headerText.length + 4));
                });

                const colTotals = {};
                header.forEach((h, idx) => {
                    if (idx >= 2) colTotals[idx] = 0;
                });

                rows.forEach(r => {
                    const values = header.map(h => r[h] ?? 0);
                    const row = ws.addRow(values);
                    row.eachCell({includeEmpty: true}, (cell, colNumber) => {
                        if (typeof cell.value === "number") {
                            cell.numFmt = '"Rp "#,##0;\\("Rp "#,##0\\)';
                            if (colNumber >= 3) {
                                colTotals[colNumber - 1] = (colTotals[colNumber - 1] || 0) + Number(cell.value || 0);
                            }
                        }
                        cell.border = fullBorder();
                    });
                });

                const totalRowValues = header.map((h, idx) => {
                    if (idx === 0) return 'TOTAL';
                    if (idx === 1) return '';
                    return colTotals[idx] || 0;
                });
                const totalRow = ws.addRow(totalRowValues);
                totalRow.eachCell({includeEmpty: true}, (cell, colNumber) => {
                    cell.font = {bold: true};
                    cell.border = fullBorder();
                    if (colNumber >= 3 && typeof cell.value === 'number') {
                        cell.numFmt = '"Rp "#,##0;\\("Rp "#,##0\\)';
                    }
                });

                const buffer = await wb.xlsx.writeBuffer();
                if (!buffer || buffer.byteLength === 0) {
                    throw new Error("Excel buffer empty");
                }
                const blob = new Blob([buffer], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                });
                const safeKelas = String(kelasVal).replace(/~/g, "-").replace(/[\\/:*?"<>|]/g, "");
                downloadBlob(blob, wbTitle + " - " + safeKelas + ".xlsx");
                return true;
            }

            function fullBorder() {
                return {
                    top: {style: "thin"},
                    left: {style: "thin"},
                    bottom: {style: "thin"},
                    right: {style: "thin"}
                };
            }

            $(document).on('click', '.btn-print-rekap', async function (e) {
                loadingAlert(`Membuat Rekap ... <br> Proses ini membutuhkan waktu beberapa saat<br><hr>
                    <p><span class="badge badge-dot bg-danger me-1"></span> Pastikan browser anda tidak memblokir <i>POP-UP</i>! </p>
                `);
                let data = $(`#${dtOptions.formId}`).serialize();
                let params;
                params = new URLSearchParams(data);

                if (params) {
                    let url = '{{route('admin.data-penerimaan.get-data-rekap')}}';
                    const form = new FormData(document.getElementById('filterForm'));
                    const params = new URLSearchParams();
                    for (const [key, value] of form.entries()) {
                        params.append(key, value);
                    }
                    const fullUrl = `${url}?${params.toString()}`;
                    const request = new Request(
                        fullUrl, {
                            method: "GET",
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': "application/json"
                            }
                        });

                    try {
                        const response = await fetch(request);

                        if (!response.ok) {
                            const status = response.status;
                            const contentType = response.headers.get('content-type');
                            let message = `Request failed with status ${status}`;
                            if (contentType && contentType.includes('application/json')) {
                                const errorData = await response.json();
                                message = errorData.message || message;
                            } else {
                                const errorText = await response.text();
                                message = errorText || message;
                            }

                            const error = new Error(message);
                            error.status = status;
                            throw error;
                        }

                        const result = await response.json();

                        if (!result['data'] || result['data'].length === 0) {
                            const error = new Error("Data Tagihan Kosong");
                            error.status = 422;
                            throw error;
                        }

                        const tableRow = generateTableRow(result.data, result.mstTagihan);
                        if (!tableRow.length) {
                            const error = new Error("Data Tagihan Kosong");
                            error.status = 422;
                            throw error;
                        }

                        await exportExcel(tableRow, params, result.mstTagihan);
                        successAlert('Sukses, Rekap telah dicetak. Cek folder Download browser Anda.');
                    } catch (error) {
                        console.error(error);
                        if (error.status === 422) {
                            const errors = error.error || error.errors;
                            errorAlert(error.message);
                            if (errors) {
                                processErrors(errors)
                            }
                        } else {
                            const errorMessages = {
                                401: 'Permintaan gagal diproses. Silakan coba lagi.',
                                403: 'Anda tidak memiliki izin untuk mengakses halaman ini 😖',
                                404: 'Halaman yang dituju tidak ditemukan 🧐',
                                405: 'Metode tidak valid 🧐 <br>silahkan muat ulang halaman dan coba lagi!',
                                419: 'Permintaan gagal diproses. Silakan coba lagi.',
                                429: 'Terlalu banyak permintaan akses <br>silahkan tunggu beberapa saat 🙏',
                            };
                            errorAlert(error.message || errorMessages[error.status] || "Terjadi kesalahan saat membuat Excel");
                        }
                    }
                } else {
                    warningAlert('Silahkan pilih Unit atau Kelas terlebih dahulu!')
                }
            });

            @if(!empty($unitScope))
            const unitScope = @json($unitScope);
            const $unitSelect = $("[name='filter[unit]']");
            if ($unitSelect.length && unitScope) {
                $unitSelect.val(unitScope).trigger('change');
                $unitSelect.find('option').each(function () {
                    if ($(this).val() !== unitScope && $(this).val() !== 'all') {
                        $(this).prop('disabled', true);
                    }
                });
            }
            @endif
        });

    </script>

    {!! ($modalLink??'') !!}
@endsection
