@extends('layouts.admin_new')
@section('style')
    <link rel="stylesheet" href="{{asset('main/libs/datatables-bs5/datatables.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/datatables-responsive-bs5/responsive.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('main/libs/datatables-buttons-bs5/buttons.bootstrap5.css')}}">
@endsection
@section('content')
    <h3 class="page-heading d-flex text-gray-900 fw-bold flex-column justify-content-center my-0">
        {{($dataTitle??($mainTitle??($title??'')))}}
    </h3>
    <ul class="breadcrumb breadcrumb-style2">
        <li class="breadcrumb-item">
            <a href="{{ route('admin.index') }}" class="text-hover-primary">Beranda</a>
        </li>
        @isset($title)
            <li class="breadcrumb-item">{{ $title }}</li>
        @endisset
        @isset($mainTitle)
            <li class="breadcrumb-item active">{{ $mainTitle }}</li>
        @endisset
    </ul>

    <div class="card">
        <div class="card-header header-elements">
            <h5 class="mb-0 me-2">{{($dataTitle??$mainTitle)}}</h5>
            <div class="card-header-elements ms-auto">
                <div class="w-100">
                    <div class="row">
                        <div class="d-flex justify-content-center justify-content-md-end gap-4">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modal-create" title="Tambah Musrifah">
                                <span class="ri-add-line me-2"></span>
                                Tambah Musrifah
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive text-nowrap">
            <table class="table table-sm table-bordered table-hover" id="main_table">
                <thead class="table-light"></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{asset('main/libs/datatables-bs5/datatables-bootstrap5.js')}}"></script>
    <script src="{{asset('js/datatableCustom/Datatable-0-4.min.js')}}"></script>
    <script src="{{asset('js/helper/errorInputHelper.min.js')}}"></script>

    <form id="addForm" class="mainForm">
        <div class="modal modal-blur fade" id="modal-create" tabindex="-1" role="dialog" aria-hidden="true"
             data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Musrifah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <fieldset class="form-fieldset">
                            <div class="mb-3">
                                <label class="form-label required" for="create_username">Username</label>
                                <input type="text" class="form-control" name="username" id="create_username" autocomplete="off"
                                       placeholder="Username" required>
                                <div class="invalid-feedback" role="alert"><strong></strong></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required" for="create_nama">Nama</label>
                                <input type="text" class="form-control" name="nama" id="create_nama" autocomplete="off"
                                       placeholder="Nama musrifah" required>
                                <div class="invalid-feedback" role="alert"><strong></strong></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required" for="create_password">Password</label>
                                <input type="password" class="form-control" name="password" id="create_password" autocomplete="new-password"
                                       placeholder="Password" required>
                                <div class="invalid-feedback" role="alert"><strong></strong></div>
                            </div>
                        </fieldset>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col">
                                    <input type="reset" value="Batal" class="btn btn-outline-secondary w-100"
                                           data-bs-dismiss="modal">
                                </div>
                                <div class="col">
                                    <input type="submit" value="Simpan" class="btn btn-primary w-100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="editForm" class="mainForm">
        <div class="modal modal-blur fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true"
             data-bs-backdrop="static">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Musrifah</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <fieldset class="form-fieldset">
                            <div class="mb-3">
                                <label class="form-label required" for="edit_username">Username</label>
                                <input type="text" class="form-control" name="username" id="edit_username" autocomplete="off"
                                       placeholder="Username" required>
                                <div class="invalid-feedback" role="alert"><strong></strong></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required" for="edit_nama">Nama</label>
                                <input type="text" class="form-control" name="nama" id="edit_nama" autocomplete="off"
                                       placeholder="Nama musrifah" required>
                                <div class="invalid-feedback" role="alert"><strong></strong></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="edit_password">Password Baru</label>
                                <input type="password" class="form-control" name="password" id="edit_password" autocomplete="new-password"
                                       placeholder="Kosongkan jika tidak diubah">
                                <div class="form-text">Kosongkan jika password tidak ingin diubah.</div>
                                <div class="invalid-feedback" role="alert"><strong></strong></div>
                            </div>
                        </fieldset>
                        <input type="hidden" id="edit_id" name="item_id" value="">
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col">
                                    <input type="reset" value="Batal" class="btn btn-outline-secondary w-100"
                                           data-bs-dismiss="modal">
                                </div>
                                <div class="col">
                                    <input type="submit" value="Simpan Perubahan" class="btn btn-primary w-100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="form-delete" class="mainForm">
        <div class="modal modal-blur fade" id="modal-delete" tabindex="-1" role="dialog" aria-hidden="true"
             data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-status bg-danger"></div>
                    <div class="modal-header">
                        <div class="modal-title">Hapus Musrifah</div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-capitalize text-center py-4">
                        <span class="ri-delete-bin-line ri-3x"></span>
                        <h4>Hapus musrifah?</h4>
                        <div>Anda yakin akan menghapus data musrifah ini?</div>
                    </div>
                    <div class="modal-body py-4">
                        <fieldset class="form-fieldset">
                            <div class="mb-3 row">
                                <label for="delete_username" class="col-sm-4 col-form-label form-label-sm">Username</label>
                                <div class="col">
                                    <input type="text" readonly class="form-control form-control-sm" id="delete_username">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="delete_nama" class="col-sm-4 col-form-label form-label-sm">Nama</label>
                                <div class="col">
                                    <input type="text" readonly class="form-control form-control-sm" id="delete_nama">
                                </div>
                            </div>
                        </fieldset>
                        <input type="hidden" id="delete_id" name="item_id" value="">
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col">
                                    <input type="reset" class="btn btn-outline-secondary w-100" value="Batal"
                                           data-bs-dismiss="modal">
                                </div>
                                <div class="col">
                                    <input type="submit" value="Hapus" class="btn btn-danger w-100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script type="text/javascript">
        const dtOptions = {
            tableId: 'main_table',
            formId: false,
            columnUrl: '{{($columnsUrl??null)}}',
            dataUrl: '{{($datasUrl??null)}}',
            dataColumns: [],
            thead: true,
            tfoot: true,
            paging: true,
            searching: true,
            fixedHeader: false,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
        };

        const modalCreate = new bootstrap.Modal(document.getElementById('modal-create'));
        const modalEdit = new bootstrap.Modal(document.getElementById('modal-edit'));
        const modalDelete = new bootstrap.Modal(document.getElementById('modal-delete'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function isEmpty(val) {
            return val === false || val === undefined || val === null || val === '' || (Array.isArray(val) && val.length === 0);
        }

        document.querySelector(`#${dtOptions.tableId} tbody`).addEventListener('click', function (e) {
            const rowEl = e.target.closest('tr');
            if (!rowEl) return;

            const rowData = DT[`${dtOptions.tableId}`].row(rowEl).data();

            if (e.target.closest('.btn-edit')) {
                document.getElementById('edit_username').value = rowData.username || '';
                document.getElementById('edit_nama').value = rowData.nama || '';
                document.getElementById('edit_password').value = '';
                document.getElementById('edit_id').value = rowData.item_id || '';
                modalEdit.show();
            }

            if (e.target.closest('.btn-hapus')) {
                document.getElementById('delete_username').value = rowData.username || '';
                document.getElementById('delete_nama').value = rowData.nama || '';
                document.getElementById('delete_id').value = rowData.item_id || '';
                modalDelete.show();
            }
        });

        document.querySelectorAll(".mainForm").forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                switch (form.id) {
                    case 'addForm':
                        submitForm('store');
                        break;
                    case 'editForm':
                        submitForm('update');
                        break;
                    case 'form-delete':
                        submitForm('delete');
                        break;
                }
            });
        });

        function submitForm(action) {
            let request, url, data;

            switch (action) {
                case 'store':
                    loadingAlert('Menyimpan data musrifah...');
                    url = '{{ route('admin.update-musrifah.store') }}';
                    data = new FormData(document.getElementById('addForm'));
                    request = new Request(url, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': csrfToken},
                        body: data,
                    });
                    break;
                case 'update':
                    loadingAlert('Mengubah data musrifah...');
                    const itemId = document.getElementById('edit_id').value;
                    if (isEmpty(itemId)) {
                        warningAlert('Data tidak valid, silahkan muat ulang halaman');
                        return;
                    }
                    url = '{{ route('admin.update-musrifah.update', ':id') }}'.replace(':id', itemId);
                    data = new FormData(document.getElementById('editForm'));
                    data.append('_method', 'PUT');
                    request = new Request(url, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': csrfToken},
                        body: data,
                    });
                    break;
                case 'delete':
                    loadingAlert('Menghapus data musrifah...');
                    const deleteId = document.getElementById('delete_id').value;
                    if (isEmpty(deleteId)) {
                        warningAlert('Data tidak valid, silahkan muat ulang halaman');
                        return;
                    }
                    url = '{{ route('admin.update-musrifah.destroy', ':id') }}'.replace(':id', deleteId);
                    request = new Request(url, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({}),
                    });
                    break;
                default:
                    errorAlert('Data tidak valid!');
                    return;
            }

            fetch(request)
                .then(async response => {
                    const result = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw {status: response.status, message: result.message || response.statusText, errors: result.errors};
                    }
                    return result;
                })
                .then(result => {
                    dataReload(dtOptions.tableId);
                    successAlert(result.message);
                    if (action === 'store') modalCreate.hide();
                    if (action === 'update') modalEdit.hide();
                    if (action === 'delete') modalDelete.hide();
                })
                .catch(error => {
                    if (error.status === 422) {
                        errorAlert(error.message);
                        if (error.errors) processErrors(error.errors);
                    } else {
                        const errorMessages = {
                            419: 'Permintaan gagal diproses. Silakan coba lagi.',
                            500: 'Tidak dapat terhubung ke server',
                        };
                        errorAlert(error.message || errorMessages[error.status] || 'Terjadi kesalahan, silahkan coba lagi');
                    }
                });
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (dtOptions.dataUrl && dtOptions.columnUrl) {
                getDT(dtOptions);
            }

            document.querySelectorAll('.modal').forEach(modal => {
                modal.addEventListener('hide.bs.modal', function () {
                    const form = modal.closest('form');
                    if (!form) return;
                    form.reset();
                    clearErrorMessages(form.id);
                });
            });
        });
    </script>
@endsection
