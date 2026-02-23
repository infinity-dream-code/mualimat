<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{route('admin.index')}}" class="app-brand-link">
              <span class="app-brand-logo demo">
                <span style="color: var(--bs-primary)">
                  <img width="50" height="50" src="{{asset('logo.png')}}" alt="logo">
                </span>
              </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">SIKEU</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M11.4854 4.88844C11.0081 4.41121 10.2344 4.41121 9.75715 4.88844L4.51028 10.1353C4.03297 10.6126 4.03297 11.3865 4.51028 11.8638L9.75715 17.1107C10.2344 17.5879 11.0081 17.5879 11.4854 17.1107C11.9626 16.6334 11.9626 15.8597 11.4854 15.3824L7.96672 11.8638C7.48942 11.3865 7.48942 10.6126 7.96672 10.1353L11.4854 6.61667C11.9626 6.13943 11.9626 5.36568 11.4854 4.88844Z"
                    fill="currentColor"
                    fill-opacity="0.6"/>
                <path
                    d="M15.8683 4.88844L10.6214 10.1353C10.1441 10.6126 10.1441 11.3865 10.6214 11.8638L15.8683 17.1107C16.3455 17.5879 17.1192 17.5879 17.5965 17.1107C18.0737 16.6334 18.0737 15.8597 17.5965 15.3824L14.0778 11.8638C13.6005 11.3865 13.6005 10.6126 14.0778 10.1353L17.5965 6.61667C18.0737 6.13943 18.0737 5.36568 17.5965 4.88844C17.1192 4.41121 16.3455 4.41121 15.8683 4.88844Z"
                    fill="currentColor"
                    fill-opacity="0.38"/>
            </svg>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-item  {{ Request::is(['admin'])  ? 'active' : '' }}">
            <a href="{{route('admin.index')}}" class="menu-link">
                <i class="menu-icon ri ri-home-3-line"></i>
                <div data-i18n="Beranda">Beranda</div>
            </a>
        </li>
        <li class="menu-item  {{ Request::is(['admin/data-tagihan'])  ? 'active' : '' }}">
            <a href="{{route('admin.data-tagihan.index')}}" class="menu-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="menu-icon icon icon-tabler icons-tabler-outline icon-tabler-list">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M9 6l11 0"/>
                    <path d="M9 12l11 0"/>
                    <path d="M9 18l11 0"/>
                    <path d="M5 6l0 .01"/>
                    <path d="M5 12l0 .01"/>
                    <path d="M5 18l0 .01"/>
                </svg>
                <div data-i18n="Data Tagihan">Data Tagihan</div>
            </a>
        </li>
        <li class="menu-item  {{ Request::is(['admin/data-penerimaan'])  ? 'active' : '' }}">
            <a href="{{route('admin.data-penerimaan.index')}}" class="menu-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="menu-icon icon icon-tabler icons-tabler-outline icon-tabler-list-check">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3.5 5.5l1.5 1.5l2.5 -2.5"/>
                    <path d="M3.5 11.5l1.5 1.5l2.5 -2.5"/>
                    <path d="M3.5 17.5l1.5 1.5l2.5 -2.5"/>
                    <path d="M11 6l9 0"/>
                    <path d="M11 12l9 0"/>
                    <path d="M11 18l9 0"/>
                </svg>
                <div data-i18n="Data Penerimaan">Data Penerimaan</div>
            </a>
        </li>

        <li class="menu-item  {{ Request::is(['admin/rekap-tagihan'])  ? 'active' : '' }}">
            <a href="{{route('admin.rekap-tagihan.index')}}" class="menu-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="menu-icon icon icon-tabler icons-tabler-outline icon-tabler-file-description">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2"/>
                    <path d="M9 17h6"/>
                    <path d="M9 13h6"/>
                </svg>
                <div data-i18n="Rekap Tagihan">Rekap Tagihan</div>
            </a>
        </li>
        <li class="menu-item  {{ Request::is(['admin/rekap-penerimaan'])  ? 'active' : '' }}">
            <a href="{{route('admin.rekap-penerimaan.index')}}" class="menu-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="menu-icon icon icon-tabler icons-tabler-outline icon-tabler-file-check">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2"/>
                    <path d="M9 15l2 2l4 -4"/>
                </svg>
                <div data-i18n="Rekap Penerimaan">Rekap Penerimaan</div>
            </a>
        </li>
        <li class="menu-item  {{ Request::is(['admin/cek-pelunasan'])  ? 'active' : '' }}">
            <a href="{{route('admin.cek-pelunasan.index')}}" class="menu-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="menu-icon icon icon-tabler icons-tabler-outline icon-tabler-list-letters">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M11 6h9"/>
                    <path d="M11 12h9"/>
                    <path d="M11 18h9"/>
                    <path d="M4 10v-4.5a1.5 1.5 0 0 1 3 0v4.5"/>
                    <path d="M4 8h3"/>
                    <path d="M4 20h1.5a1.5 1.5 0 0 0 0 -3h-1.5h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6"/>
                </svg>
                <div data-i18n="Data Penerimaan">Cek Pelunasan</div>
            </a>
        </li>
        <li class="menu-item  {{ Request::is(['admin/rekap-cek-pelunasan'])  ? 'active' : '' }}">
            <a href="{{route('admin.rekap-cek-pelunasan.index')}}" class="menu-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="menu-icon icon icon-tabler icons-tabler-outline icon-tabler-checkup-list">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/>
                    <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z"/>
                    <path d="M9 14h.01"/>
                    <path d="M9 17h.01"/>
                    <path d="M12 16l1 1l3 -3"/>
                </svg>
                <div data-i18n="Rekap Cek Penerimaan">Rekap Cek Pelunasan</div>
            </a>
        </li>
        <li class="menu-item  {{ Request::is(['admin/rekap-saldo'])  ? 'active' : '' }}">
            <a href="{{route('admin.rekap-saldo.index')}}" class="menu-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="menu-icon icon icon-tabler icons-tabler-outline icon-tabler-report-money">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"/>
                    <path d="M9 5a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2"/>
                    <path d="M14 11h-2.5a1.5 1.5 0 0 0 0 3h1a1.5 1.5 0 0 1 0 3h-2.5"/>
                    <path d="M12 17v1m0 -8v1"/>
                </svg>
                <div data-i18n="Rekap SAldo">Rekap Saldo</div>
            </a>
        </li>

        <li class="menu-item {{ Request::is(['admin/potongan-tagihan*'])  ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="menu-icon icon icon-tabler icons-tabler-outline icon-tabler-file-scissors">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2"/>
                    <path d="M14 17a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
                    <path d="M8 17a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"/>
                    <path d="M9 17l6 -6"/>
                    <path d="M15 17l-6 -6"/>
                </svg>
                <div data-i18n="Potongan Tagihan">Potongan Tagihan</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is(['admin/potongan-tagihan'])  ? 'active' : '' }}">
                    <a href="{{route('admin.potongan-tagihan.index')}}" class="menu-link">
                        <div data-i18n="Data Potongan Tagihan">Data Potongan Tagihan</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is(['admin/potongan-tagihan/create'])  ? 'active' : '' }}">
                    <a href="{{route('admin.potongan-tagihan.create')}}" class="menu-link">
                        <div data-i18n="Buat Potongan Tagihan">Buat Potongan Tagihan</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item mt-auto pb-2">
            <a href="{{route('logout')}}" class="menu-link btn-danger text-white">
                <i class="menu-icon ri ri-logout-box-r-line"></i>
                <div data-i18n="Logout">
                    Logout
                </div>
            </a>
        </li>
    </ul>
</aside>
