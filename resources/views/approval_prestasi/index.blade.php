@extends('layouts.login_new')
@section('title', 'Approval Prestasi')

@section('style')
    <link rel="stylesheet" href="{{ asset('main/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <style>
        .table-wrap {
            max-height: 72vh;
            overflow: auto;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl py-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-start gap-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Approval Prestasi</h4>
                    <div class="text-muted small">
                        Login: {{ $authUser['nama'] !== '' ? $authUser['nama'] : $authUser['username'] }}
                        | Role: {{ $authUser['role'] ?: '-' }}
                        | CODE01: {{ $authUser['code01'] !== '' ? $authUser['code01'] : 'SEMUA' }}
                    </div>
                </div>
                <form method="POST" action="{{ route('approval-prestasi.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Logout</button>
                </form>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select id="statusFilter" class="form-select">
                            <option value="all">Semua</option>
                            <option value="0">Pending</option>
                            <option value="1">Approved</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button id="btnReload" class="btn btn-primary w-100">Muat Data</button>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="table table-sm table-bordered table-hover align-middle" id="approvalTable">
                        <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Sekolah</th>
                            <th>Kelas</th>
                            <th>Jenis Prestasi</th>
                            <th>Keterangan</th>
                            <th>Nilai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('main/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script>
        const csrfToken = @json(csrf_token());
        const dataUrl = @json(route('approval-prestasi.data'));
        const approveUrlTemplate = @json(route('approval-prestasi.approve', ['id' => '__ID__']));
        const rejectUrlTemplate = @json(route('approval-prestasi.reject', ['id' => '__ID__']));

        const tableEl = document.getElementById('approvalTable');
        const tbodyEl = tableEl.querySelector('tbody');
        const statusFilterEl = document.getElementById('statusFilter');
        const btnReloadEl = document.getElementById('btnReload');

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function badgeStatus(isapproved) {
            if (Number(isapproved) === 1) {
                return '<span class="badge rounded-pill bg-label-success">Approved</span>';
            }
            return '<span class="badge rounded-pill bg-label-warning">Pending</span>';
        }

        async function loadData() {
            btnReloadEl.disabled = true;
            btnReloadEl.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memuat';
            try {
                const url = new URL(dataUrl, window.location.origin);
                url.searchParams.set('isapproved', statusFilterEl.value);
                const response = await fetch(url.toString(), {headers: {'Accept': 'application/json'}});
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.message || 'Gagal memuat data');
                }

                const items = Array.isArray(result.items) ? result.items : [];
                let html = '';
                items.forEach((item, index) => {
                    const id = Number(item.id || 0);
                    const approved = Number(item.isapproved || 0) === 1;
                    const actionButtons = approved
                        ? `<button class="btn btn-sm btn-outline-danger btn-reject" data-id="${id}">Tolak</button>`
                        : `<button class="btn btn-sm btn-success btn-approve" data-id="${id}">Approve</button>
                           <button class="btn btn-sm btn-outline-danger btn-reject" data-id="${id}">Tolak</button>`;

                    html += `<tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(item.nocust || '-')}</td>
                        <td>${escapeHtml(item.nmcust || '-')}</td>
                        <td>${escapeHtml(item.sekolah || '-')} (${escapeHtml(item.code01 || '-')})</td>
                        <td>${escapeHtml(item.kelas || '-')}</td>
                        <td>${escapeHtml(item.jenis_prestasi || '-')}</td>
                        <td>${escapeHtml(item.keterangan || '-')}</td>
                        <td>${Number(item.nilai_penghargaan || 0).toLocaleString('id-ID')}</td>
                        <td>${badgeStatus(item.isapproved)}</td>
                        <td class="text-nowrap">${actionButtons}</td>
                    </tr>`;
                });
                tbodyEl.innerHTML = html || '<tr><td colspan="10" class="text-center text-muted">Data kosong</td></tr>';
            } catch (error) {
                tbodyEl.innerHTML = `<tr><td colspan="10" class="text-center text-danger">${escapeHtml(error.message || 'Terjadi kesalahan')}</td></tr>`;
            } finally {
                btnReloadEl.disabled = false;
                btnReloadEl.textContent = 'Muat Data';
            }
        }

        async function updateApproval(id, action) {
            const urlTemplate = action === 'approve' ? approveUrlTemplate : rejectUrlTemplate;
            const url = urlTemplate.replace('__ID__', String(id));
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(result.message || 'Gagal menyimpan approval');
            }
            return result;
        }

        tbodyEl.addEventListener('click', async (event) => {
            const approveBtn = event.target.closest('.btn-approve');
            const rejectBtn = event.target.closest('.btn-reject');
            if (!approveBtn && !rejectBtn) return;

            const button = approveBtn || rejectBtn;
            const action = approveBtn ? 'approve' : 'reject';
            const id = Number(button.getAttribute('data-id') || 0);
            if (!id) return;

            button.disabled = true;
            try {
                await updateApproval(id, action);
                await loadData();
            } catch (error) {
                alert(error.message || 'Terjadi kesalahan');
            } finally {
                button.disabled = false;
            }
        });

        btnReloadEl.addEventListener('click', loadData);
        statusFilterEl.addEventListener('change', loadData);
        loadData();
    </script>
@endsection

