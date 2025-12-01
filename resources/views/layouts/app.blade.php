<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Supervisi Digital' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            try {
                if (window.innerWidth >= 1024 && localStorage.getItem('sidebar-collapsed') === '1') {
                    document.documentElement.classList.add('prefers-collapsed');
                }
            } catch (error) {
                /* ignore */
            }
        })();
    </script>
    <style>
        :root {
            --sidebar-expanded-width: 18rem;
            --sidebar-collapsed-width: 5rem;
            --sidebar-handle-offset: 1.25rem;
        }

        @media (min-width: 1024px) {
            #sidebar {
                width: var(--sidebar-expanded-width);
                transition: width 0.25s ease-in-out, padding 0.25s ease-in-out;
                will-change: width, padding;
            }

            html.prefers-collapsed body #sidebar,
            body.sidebar-collapsed #sidebar {
                width: var(--sidebar-collapsed-width);
                padding-left: 1rem;
                padding-right: 1rem;
            }

            html.prefers-collapsed body #sidebar nav a,
            body.sidebar-collapsed #sidebar nav a {
                gap: 0;
                justify-content: center;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            html.prefers-collapsed body #sidebar nav a .sidebar-label,
            html.prefers-collapsed body #sidebar nav a .sidebar-indicator,
            html.prefers-collapsed body #sidebar .sidebar-guest,
            body.sidebar-collapsed #sidebar nav a .sidebar-label,
            body.sidebar-collapsed #sidebar nav a .sidebar-indicator,
            body.sidebar-collapsed #sidebar .sidebar-guest {
                display: none;
            }

            html.prefers-collapsed body #sidebar nav a svg,
            body.sidebar-collapsed #sidebar nav a svg {
                margin-right: 0;
            }

            .layout-content {
                padding-left: var(--sidebar-expanded-width);
                transition: padding-left 0.25s ease-in-out;
                will-change: padding-left;
                max-width: 100vw;
                overflow-x: hidden;
            }

            html.prefers-collapsed body .layout-content,
            body.sidebar-collapsed .layout-content {
                padding-left: var(--sidebar-collapsed-width);
            }

            #sidebar-collapse-handle {
                position: fixed;
                top: 50%;
                left: calc(var(--sidebar-expanded-width) - 0.5rem);
                transform: translate(-50%, -50%);
                z-index: 60;
                transition: left 0.25s ease-in-out;
                border-radius: 9999px;
                padding: 0.25rem;
                box-shadow: 0 10px 30px -12px rgba(15, 23, 42, 0.45);
            }

            html.prefers-collapsed body #sidebar-collapse-handle,
            body.sidebar-collapsed #sidebar-collapse-handle {
                left: calc(var(--sidebar-collapsed-width) - 0.5rem);
                border-radius: 9999px;
            }
        }

        @media (max-width: 1023.98px) {
            #sidebar-collapse-handle {
                display: none !important;
            }
        }

        [data-sidebar-collapse] .icon-collapsed {
            display: none;
        }

        body.is-uploading #sidebar-collapse-handle,
        body.is-uploading [data-sidebar-toggle] {
            opacity: 0 !important;
            pointer-events: none !important;
        }

        html.prefers-collapsed body [data-sidebar-collapse] .icon-expanded,
        body.sidebar-collapsed [data-sidebar-collapse] .icon-expanded {
            display: none;
        }

        html.prefers-collapsed body [data-sidebar-collapse] .icon-collapsed,
        body.sidebar-collapsed [data-sidebar-collapse] .icon-collapsed {
            display: inline-flex;
        }
    </style>
</head>
@php
    $user = Auth::user();
    $isAdmin = $user?->is_admin;
    $isSupervisor = $user ? $user->schools()->wherePivot('role', 'supervisor')->exists() : false;
    $isTeacher = $user ? $user->schools()->wherePivot('role', 'teacher')->exists() : false;

    $dashboardRoute = $isAdmin
        ? route('admin.dashboard')
        : ($isSupervisor
            ? route('supervisor.dashboard')
            : ($isTeacher ? route('guru.dashboard') : null));

    $sidebarItems = collect();
    if ($user) {
        $sidebarItems = $sidebarItems->merge([
            [
                'label' => 'Dashboard',
                'route' => $dashboardRoute ?? route('profile.index'),
                'active' => $dashboardRoute ? request()->url() === $dashboardRoute : request()->routeIs('profile.index'),
                'icon' => 'layout-dashboard',
            ],
        ]);

        if ($isAdmin) {
            $sidebarItems = $sidebarItems->merge([
                [
                    'label' => 'Pengguna',
                    'route' => route('admin.users.index'),
                    'active' => request()->routeIs('admin.users.*'),
                    'icon' => 'users',
                ],
                [
                    'label' => 'Sekolah',
                    'route' => route('admin.schools.index'),
                    'active' => request()->routeIs('admin.schools.*'),
                    'icon' => 'buildings',
                ],
            ]);
        }

        if ($isSupervisor) {
            $sidebarItems = $sidebarItems->merge([
                [
                    'label' => 'Jadwal Supervisi',
                    'route' => route('supervisor.schedules'),
                    'active' => request()->routeIs('supervisor.schedules*'),
                    'icon' => 'calendar',
                ],
                [
                    'label' => 'Data Guru',
                    'route' => route('supervisor.users.index'),
                    'active' => request()->routeIs('supervisor.users.*'),
                    'icon' => 'graduation-cap',
                ],
            ]);
        }

        if ($isTeacher) {
            $sidebarItems = $sidebarItems->merge([
                [
                    'label' => 'Jadwal Saya',
                    'route' => route('guru.schedules'),
                    'active' => request()->routeIs('guru.schedules'),
                    'icon' => 'calendar-days',
                ],
            ]);
        }

        $sidebarItems = $sidebarItems->merge([
            [
                'label' => 'Profil',
                'route' => route('profile.index'),
                'active' => request()->routeIs('profile.*'),
                'icon' => 'user-circle',
            ],
        ]);
    }
@endphp
<body class="min-h-full bg-[#F9FAFB] font-sans text-slate-900 antialiased">
    <div class="relative flex min-h-screen bg-[#F9FAFB]/60">
        <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-slate-900/30 backdrop-blur-sm transition-all duration-300 ease-in-out"></div>

        <aside id="sidebar" class="fixed top-16 bottom-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-slate-200 bg-white/80 px-6 py-8 backdrop-blur-md transition-all duration-300 ease-in-out lg:translate-x-0">
            @auth
                <nav class="space-y-2 text-sm">
                    @foreach ($sidebarItems as $item)
                        <a href="{{ $item['route'] }}" title="{{ $item['label'] }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 font-medium transition-all duration-300 ease-in-out {{ $item['active'] ? 'bg-indigo-50 text-indigo-600 shadow-sm shadow-indigo-100' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                            @include('layouts.partials.icon', ['name' => $item['icon'], 'classes' => 'h-5 w-5 transition-all duration-300 ease-in-out ' . ($item['active'] ? 'text-indigo-500' : 'text-slate-400 group-hover:text-slate-500')])
                            <span class="sidebar-label">{{ $item['label'] }}</span>
                            @if ($item['active'])
                                <span class="sidebar-indicator ml-auto h-2 w-2 rounded-full bg-indigo-400"></span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            @endauth

            @guest
                <div class="space-y-4 sidebar-guest">
                    <p class="text-sm text-slate-500">Silakan masuk untuk mengakses dashboard Supervisi Digital.</p>
                    <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-indigo-500 to-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-md shadow-indigo-200/50 transition-all duration-300 ease-in-out hover:opacity-90">Masuk</a>
                </div>
            @endguest
        </aside>

        @auth
            <button type="button" id="sidebar-collapse-handle" class="hidden h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 shadow-md shadow-slate-200/70 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-500 lg:flex" data-sidebar-collapse aria-label="Collapse sidebar" aria-pressed="false">
                <span class="icon-expanded">
                    @include('layouts.partials.icon', ['name' => 'chevron-left', 'classes' => 'h-5 w-5'])
                </span>
                <span class="icon-collapsed">
                    @include('layouts.partials.icon', ['name' => 'chevron-right', 'classes' => 'h-5 w-5'])
                </span>
            </button>
        @endauth

        <div id="layout-content" class="layout-content flex min-h-screen flex-1 flex-col">
            <header class="fixed inset-x-0 top-0 z-50 border-b border-slate-200/80 bg-white/80 backdrop-blur-md">
                <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
                    <div class="flex items-center gap-4">
                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-indigo-500 lg:hidden" data-sidebar-toggle aria-label="Toggle navigation">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                <path d="M4 6.5h16" />
                                <path d="M4 12h16" />
                                <path d="M4 17.5h16" />
                            </svg>
                        </button>
                        <a href="{{ $dashboardRoute ?? route('profile.index') }}" class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500 text-white shadow-md shadow-indigo-200/60">SD</span>
                            <span class="hidden sm:block">Supervisi Digital</span>
                        </a>
                    </div>

                    <div class="flex items-center gap-3">
                        @auth
                            <div class="relative" data-profile-menu>
                                <button type="button" data-profile-trigger class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 text-left text-sm font-medium text-slate-600 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:text-slate-900">
                                    <span class="hidden text-xs text-slate-400 sm:block">Hai,</span>
                                    <span class="text-sm font-semibold text-slate-900">{{ Str::limit($user->name, 18) }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-slate-400 transition-all duration-300 ease-in-out">
                                        <path d="M6.5 9.5 12 15l5.5-5.5" />
                                    </svg>
                                </button>
                                <div data-profile-dropdown class="pointer-events-none absolute right-0 top-full mt-2 w-64 scale-95 transform rounded-xl border border-slate-200 bg-white/95 p-3 text-sm opacity-0 shadow-xl shadow-slate-200/60 transition-all duration-300 ease-out">
                                    <div class="rounded-lg bg-slate-50/80 p-3">
                                        <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                    <a href="{{ route('profile.index') }}" class="mt-2 flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-all duration-300 ease-in-out hover:bg-slate-100 hover:text-slate-900">
                                        @include('layouts.partials.icon', ['name' => 'user-circle', 'classes' => 'h-4 w-4 text-slate-400'])
                                        Profil Saya
                                    </a>
                                    <form action="{{ route('logout') }}" method="post" class="mt-2">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center justify-between rounded-lg bg-rose-500 px-3 py-2 text-sm font-semibold text-white shadow-md shadow-rose-200/70 transition-all duration-300 ease-in-out hover:bg-rose-600">
                                            <span>Keluar</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                                <path d="M15.5 6V4.5A1.5 1.5 0 0 0 14 3H6.5A1.5 1.5 0 0 0 5 4.5v15A1.5 1.5 0 0 0 6.5 21h7.5a1.5 1.5 0 0 0 1.5-1.5V18" />
                                                <path d="M10 12h9" />
                                                <path d="m16.5 8.5 3.5 3.5-3.5 3.5" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endauth

                        @guest
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-600 shadow-sm shadow-indigo-100 transition-all duration-300 ease-in-out hover:bg-indigo-50">Masuk</a>
                        @endguest
                    </div>
                </div>
            </header>

            <main class="flex-1 bg-[#F9FAFB] pt-24">
                <div class="mx-auto w-full max-w-7xl px-6 pb-16">
                    @if (session('success') || session('error') || session('info') || session('warning'))
                        <div id="toast-root" class="relative mb-8 space-y-3">
                            @foreach (['success' => 'Berhasil', 'error' => 'Terjadi Kesalahan', 'warning' => 'Perhatian', 'info' => 'Informasi'] as $type => $label)
                                @if (session($type))
                                    <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white/90 p-4 text-sm shadow-lg shadow-slate-200/50 transition-all duration-300 ease-in-out hover:-translate-y-0.5 hover:shadow-xl" data-toast="{{ $type }}">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg {{ $type === 'success' ? 'bg-emerald-50 text-emerald-600' : ($type === 'error' ? 'bg-rose-50 text-rose-500' : ($type === 'warning' ? 'bg-amber-50 text-amber-500' : 'bg-blue-50 text-indigo-500')) }}">
                                            @include('layouts.partials.icon', ['name' => $type === 'success' ? 'check-circle' : ($type === 'error' ? 'alert-triangle' : ($type === 'warning' ? 'shield-alert' : 'sparkles')), 'classes' => 'h-5 w-5'])
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-slate-900">{{ $label }}</p>
                                            <p class="mt-1 text-sm text-slate-600">{{ session($type) }}</p>
                                        </div>
                                        <button type="button" data-toast-dismiss class="rounded-lg bg-slate-100 px-3 py-1 text-xs font-medium text-slate-500 transition-all duration-300 ease-in-out hover:bg-slate-200">Tutup</button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm transition-all duration-300 ease-in-out">
        <div class="w-full max-w-md scale-95 transform rounded-xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-200/60 transition-all duration-300 ease-in-out">
            <div class="flex items-center gap-3">
                <div id="confirm-icon" class="flex h-11 w-11 items-center justify-center rounded-lg bg-rose-50 text-rose-500">
                    @include('layouts.partials.icon', ['name' => 'shield-alert', 'classes' => 'h-6 w-6'])
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Konfirmasi</h3>
                    <p class="text-sm text-slate-500" id="confirm-message">Apakah Anda yakin ingin melanjutkan?</p>
                </div>
            </div>
            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" id="confirm-cancel" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 transition-all duration-300 ease-in-out hover:border-slate-300 hover:text-slate-900">Batal</button>
                <button type="button" id="confirm-approve" class="rounded-xl bg-gradient-to-r from-rose-500 to-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-rose-200/70 transition-all duration-300 ease-in-out hover:opacity-90">Lanjutkan</button>
            </div>
        </div>
    </div>

    <div id="viewer-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm transition-all duration-300 ease-in-out">
        <div class="w-full max-w-xl scale-95 transform rounded-xl border border-slate-200 bg-white p-6 shadow-2xl shadow-slate-200/60 transition-all duration-300 ease-in-out">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Pratinjau Undangan</h3>
                    <p class="mt-1 text-sm text-slate-500">Salin tautan undangan dan bagikan kepada calon pengguna.</p>
                </div>
                <button type="button" id="viewer-close" class="rounded-xl bg-slate-100 p-2 text-slate-400 transition-all duration-300 ease-in-out hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                        <path d="M6.5 6.5 17.5 17.5" />
                        <path d="M6.5 17.5 17.5 6.5" />
                    </svg>
                </button>
            </div>
            <div class="mt-6 space-y-4">
                <label class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Link Undangan</label>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <input id="viewer-input" type="text" readonly class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                    <button type="button" id="viewer-copy" class="inline-flex items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-600 shadow-sm shadow-indigo-100 transition-all duration-300 ease-in-out hover:bg-indigo-50">Salin</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const sidebarTriggers = document.querySelectorAll('[data-sidebar-toggle]');
            const collapseToggle = document.querySelector('[data-sidebar-collapse]');
            const profileMenu = document.querySelector('[data-profile-menu]');
            const profileTrigger = profileMenu ? profileMenu.querySelector('[data-profile-trigger]') : null;
            const profileDropdown = profileMenu ? profileMenu.querySelector('[data-profile-dropdown]') : null;
            const confirmModal = document.getElementById('confirm-modal');
            const confirmCancel = document.getElementById('confirm-cancel');
            const confirmApprove = document.getElementById('confirm-approve');
            const confirmMessage = document.getElementById('confirm-message');
            const confirmIcon = document.getElementById('confirm-icon');
            const viewerModal = document.getElementById('viewer-modal');
            const viewerClose = document.getElementById('viewer-close');
            const viewerInput = document.getElementById('viewer-input');
            const viewerCopy = document.getElementById('viewer-copy');
            let confirmForm = null;

            const openSidebar = () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            };

            const closeSidebar = () => {
                if (window.innerWidth >= 1024) return;
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            };

            sidebarTriggers.forEach((trigger) => trigger.addEventListener('click', openSidebar));
            overlay.addEventListener('click', closeSidebar);
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.add('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                }
            });

        const applyCollapsed = (collapsed) => {
            document.documentElement.classList.remove('prefers-collapsed');
            document.body.classList.toggle('sidebar-collapsed', collapsed);
            if (collapseToggle) {
                collapseToggle.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
            }
        };

        const getStoredCollapseState = () => localStorage.getItem('sidebar-collapsed') === '1';

        if (collapseToggle) {
            // Apply state immediately based on stored preference (default false)
            applyCollapsed(window.innerWidth >= 1024 && getStoredCollapseState());

            collapseToggle.addEventListener('click', () => {
                const shouldCollapse = !document.body.classList.contains('sidebar-collapsed');
                applyCollapsed(shouldCollapse);
                localStorage.setItem('sidebar-collapsed', shouldCollapse ? '1' : '0');
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    applyCollapsed(getStoredCollapseState());
                } else {
                    applyCollapsed(false);
                }
            });

            document.addEventListener('DOMContentLoaded', () => {
                if (window.innerWidth >= 1024) {
                    applyCollapsed(getStoredCollapseState());
                }
            });
        }

            if (profileTrigger && profileDropdown) {
                let open = false;
                const showDropdown = () => {
                    profileDropdown.classList.remove('pointer-events-none', 'opacity-0', 'scale-95');
                    profileDropdown.classList.add('pointer-events-auto', 'opacity-100', 'scale-100');
                };
                const hideDropdown = () => {
                    profileDropdown.classList.add('pointer-events-none', 'opacity-0', 'scale-95');
                    profileDropdown.classList.remove('pointer-events-auto', 'opacity-100', 'scale-100');
                };
                profileTrigger.addEventListener('click', (event) => {
                    event.stopPropagation();
                    open = !open;
                    open ? showDropdown() : hideDropdown();
                });
                document.addEventListener('click', (event) => {
                    if (!profileMenu.contains(event.target)) {
                        open = false;
                        hideDropdown();
                    }
                });
            }

            document.querySelectorAll('[data-toast-dismiss]').forEach((button) => {
                button.addEventListener('click', () => {
                    const toast = button.closest('[data-toast]');
                    if (toast) {
                        toast.classList.add('translate-y-2', 'opacity-0');
                        setTimeout(() => toast.remove(), 180);
                    }
                });
            });

            const confirmVariants = {
                success: {
                    classes: 'from-emerald-500 to-emerald-600',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><circle cx="12" cy="12" r="9"></circle><path d="m9 12.5 2.2 2.2 4.3-4.3"></path></svg>',
                    wrapper: 'bg-emerald-50 text-emerald-500',
                },
                info: {
                    classes: 'from-indigo-500 to-blue-500',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><path d="M9.8 15.9 9 18.7l-.8-2.8a4.6 4.6 0 0 0-3.1-3.1L2.4 12l2.7-.8a4.6 4.6 0 0 0 3.1-3.1L9 5.3l.8 2.8a4.6 4.6 0 0 0 3.1 3.1l2.8.8-2.8.8a4.6 4.6 0 0 0-3.1 3.1z"></path><path d="m17 5 1 2.5 2.5 1L18 9l-1 2.5-1-2.5-2.5-1L17 7.5z"></path><path d="m17 14 1 2 2 1-2 1-1 2-1-2-2-1 2-1z"></path></svg>',
                    wrapper: 'bg-blue-50 text-indigo-500',
                },
                warning: {
                    classes: 'from-amber-500 to-amber-600',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><path d="M10.3 4.3 2.7 18a1.5 1.5 0 0 0 1.3 2.2h16a1.5 1.5 0 0 0 1.3-2.2L13.7 4.3a1.5 1.5 0 0 0-2.6 0z"></path><path d="M12 9.5v4.5"></path><circle cx="12" cy="16.5" r="0.7" fill="currentColor" stroke="none"></circle></svg>',
                    wrapper: 'bg-amber-50 text-amber-500',
                },
                danger: {
                    classes: 'from-rose-500 to-rose-600',
                    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><path d="M12 3.2 5 6v6.5c0 4.4 3.4 7.8 7 8.8 3.6-1 7-4.4 7-8.8V6l-7-2.8z"></path><path d="M12 9.5v4"></path><circle cx="12" cy="15.5" r="0.7" fill="currentColor" stroke="none"></circle></svg>',
                    wrapper: 'bg-rose-50 text-rose-500',
                },
            };

            const setConfirmVariant = (variant) => {
                const config = confirmVariants[variant] || confirmVariants.danger;
                confirmApprove.className = `rounded-xl bg-gradient-to-r ${config.classes} px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-slate-200/70 transition-all duration-300 ease-in-out hover:opacity-90`;
                confirmIcon.className = `flex h-11 w-11 items-center justify-center rounded-lg ${config.wrapper}`;
                confirmIcon.innerHTML = config.icon;
            };

            const openConfirm = (message, variant) => {
                confirmMessage.textContent = message || 'Apakah Anda yakin ingin melanjutkan?';
                setConfirmVariant(variant || 'danger');
                confirmModal.classList.remove('hidden');
            };

            const closeConfirm = () => {
                confirmModal.classList.add('hidden');
                confirmForm = null;
            };

            confirmCancel?.addEventListener('click', closeConfirm);
            confirmModal.addEventListener('click', (event) => {
                if (event.target === confirmModal) {
                    closeConfirm();
                }
            });
            confirmApprove?.addEventListener('click', () => {
                if (confirmForm) {
                    confirmForm.removeAttribute('data-intercepted');
                    confirmForm.submit();
                }
                closeConfirm();
            });

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }
                if (!form.classList.contains('js-confirm')) {
                    return;
                }
                if (form.getAttribute('data-intercepted') === '1') {
                    return;
                }
                event.preventDefault();
                form.setAttribute('data-intercepted', '1');
                confirmForm = form;
                openConfirm(form.getAttribute('data-message'), form.getAttribute('data-variant'));
            }, true);

            const openViewer = (link) => {
                viewerInput.value = link || '';
                viewerModal.classList.remove('hidden');
            };

            const closeViewer = () => {
                viewerModal.classList.add('hidden');
                viewerInput.value = '';
            };

            viewerClose?.addEventListener('click', closeViewer);
            viewerModal.addEventListener('click', (event) => {
                if (event.target === viewerModal) {
                    closeViewer();
                }
            });

            viewerCopy?.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(viewerInput.value);
                    viewerCopy.textContent = 'Disalin';
                    setTimeout(() => (viewerCopy.textContent = 'Salin'), 1500);
                } catch (error) {
                    viewerCopy.textContent = 'Gagal';
                    setTimeout(() => (viewerCopy.textContent = 'Salin'), 1500);
                }
            });

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('.js-view-link');
                if (!trigger) {
                    return;
                }
                event.preventDefault();
                openViewer(trigger.getAttribute('data-link'));
            });

            document.addEventListener('click', async (event) => {
                const trigger = event.target.closest('.js-copy-link');
                if (!trigger) {
                    return;
                }
                event.preventDefault();
                try {
                    await navigator.clipboard.writeText(trigger.getAttribute('data-link') || '');
                    trigger.textContent = 'Disalin';
                    setTimeout(() => (trigger.textContent = 'Salin'), 1500);
                } catch (error) {
                    trigger.textContent = 'Gagal';
                    setTimeout(() => (trigger.textContent = 'Salin'), 1500);
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
