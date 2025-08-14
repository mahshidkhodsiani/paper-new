<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سیستم جامع مدیریت فرآیند تولید</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- Persian Datepicker Files -->
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://unpkg.com/jalali-moment/dist/jalali-moment.browser.js"></script>
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background-color: #f1f5f9;
        }

        .kanban-column {
            min-width: 340px;
            width: 340px;
            height: calc(100vh - 380px);
            display: flex;
            flex-direction: column;
        }

        .kanban-column-header {
            border-bottom: 4px solid;
        }

        .order-list {
            flex-grow: 1;
            overflow-y: auto;
            padding: 8px;
            transition: background-color 0.2s ease;
        }

        .order-list.drag-over {
            background-color: #e0e7ff;
        }

        .modal-backdrop {
            transition: opacity 0.3s ease;
        }

        .modal-content {
            transition: transform 0.3s ease;
            height: 90vh;
            max-height: 850px;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .modal-body {
            overflow-y: auto;
            flex-grow: 1;
        }

        .card {
            background-color: #ffffff;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #e5e7eb;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07);
        }

        .order-card {
            border-right-width: 5px;
            cursor: grab;
        }

        .order-card:active {
            cursor: grabbing;
        }

        .order-card.dragging {
            opacity: 0.5;
            transform: scale(0.95);
        }

        .progress-bar {
            background-color: #e5e7eb;
            border-radius: 9999px;
            overflow: hidden;
            height: 8px;
        }

        .progress-bar-fill {
            background-color: #4ade80;
            height: 100%;
            transition: width 0.5s ease-in-out;
        }

        .view-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: background-color 0.2s, color 0.2s;
            border: 1px solid transparent;
        }

        .view-btn.active {
            background-color: #4f46e5;
            color: #ffffff;
            box-shadow: 0 2px 5px rgba(79, 70, 229, 0.3);
        }

        .view-btn:not(.active) {
            background-color: #eef2ff;
            color: #4338ca;
        }

        .view-btn:not(.active):hover {
            background-color: #e0e7ff;
        }

        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .toast {
            transition: transform 0.4s ease-in-out, opacity 0.4s ease-in-out;
        }

        .tab-button {
            padding: 0.5rem 1rem;
            border-bottom: 2px solid transparent;
            font-weight: 500;
        }

        .tab-button.active {
            border-color: #4f46e5;
            color: #4f46e5;
        }

        .dependency-group-1 {
            border-right: 4px solid #facc15;
        }

        .dependency-group-2 {
            border-right: 4px solid #ec4899;
        }

        .dependency-group-3 {
            border-right: 4px solid #6366f1;
        }

        .dependency-group-4 {
            border-right: 4px solid #22c55e;
        }

        .dependency-group-5 {
            border-right: 4px solid #a855f7;
        }

        .modal-close-btn {
            position: absolute;
            top: 1rem;
            left: 1rem;
            font-size: 1.5rem;
            color: #9ca3af;
            cursor: pointer;
            z-index: 10;
        }

        .modal-close-btn:hover {
            color: #1f2937;
        }

        .timeline-item:last-child .timeline-line {
            display: none;
        }
    </style>
</head>

<body class="bg-slate-100 text-gray-800">

    <div id="appContainer" class="">
        <header class="bg-white shadow-md sticky top-0 z-20">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <h1 class="text-2xl font-bold text-gray-900">مدیریت فرآیند تولید</h1>
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <span id="currentUser" class="text-sm text-gray-500"></span>
                    </div>
                </div>
            </div>
        </header>

        <section class="container mx-auto p-4 sm:p-6 lg:p-8 pb-0 space-y-4">
            <div class="bg-white p-4 rounded-xl shadow-sm border flex justify-between items-center flex-wrap gap-4">
                <div class="flex-grow flex items-center space-x-1 space-x-reverse flex-wrap gap-2">
                    <button data-view="dashboardView" class="view-btn"><i class="fas fa-chart-line ml-2"></i>داشبورد</button>
                    <button data-view="ordersView" class="view-btn"><i class="fas fa-grip-vertical ml-2"></i>سفارش‌ها</button>
                    <button data-view="projectsView" class="view-btn"><i class="fas fa-sitemap ml-2"></i>پروژه‌ها</button>
                    <button data-view="quotingView" class="view-btn"><i class="fas fa-comments-dollar ml-2"></i>قیمت‌دهی</button>
                    <button data-view="shippingView" class="view-btn"><i class="fas fa-shipping-fast ml-2"></i>حمل و نقل</button>
                    <button data-view="accountingView" class="view-btn"><i class="fas fa-cash-register ml-2"></i>حسابداری</button>
                    <button data-view="archiveView" class="view-btn"><i class="fas fa-archive ml-2"></i>بایگانی</button>
                    <button data-view="settingsView" class="view-btn"><i class="fas fa-gear ml-2"></i>تنظیمات</button>
                </div>
                <div id="main-action-buttons" class="flex items-center space-x-2 space-x-reverse"></div>
            </div>
            <div id="global-filters" class="bg-white p-3 rounded-xl shadow-sm border flex items-center gap-4 flex-wrap hidden">
                <span class="font-semibold text-gray-600">فیلترها:</span>
                <div class="flex-grow" style="min-width: 150px;">
                    <select id="customer-filter" class="w-full p-2 border rounded-md bg-white text-sm">
                        <option value="">همه مشتریان</option>
                    </select>
                </div>
                <div class="flex-grow" style="min-width: 150px;">
                    <select id="producer-filter" class="w-full p-2 border rounded-md bg-white text-sm">
                        <option value="">همه سازندگان</option>
                    </select>
                </div>
                <div class="flex-grow hidden" style="min-width: 150px;" id="project-status-filter-container">
                    <select id="project-status-filter" class="w-full p-2 border rounded-md bg-white text-sm">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="active">فعال</option>
                        <option value="completed">تکمیل شده</option>
                    </select>
                </div>
                <div class="flex-grow hidden" style="min-width: 150px;" id="archive-type-filter-container">
                    <select id="archive-type-filter" class="w-full p-2 border rounded-md bg-white text-sm">
                        <option value="">همه انواع</option>
                        <option value="masterProjects">پروژه</option>
                        <option value="orders">سفارش</option>
                        <option value="quotationRequests">درخواست قیمت</option>
                        <option value="mainShipments">محموله</option>
                    </select>
                </div>
                <div class="flex-grow" style="min-width: 160px;">
                    <select id="sort-order" class="w-full p-2 border rounded-md bg-white text-sm">
                        <option value="updatedAt_desc">مرتب‌سازی: آخرین بروزرسانی</option>
                        <option value="createdAt_desc">مرتب‌سازی: جدیدترین</option>
                        <option value="updatedAt_asc">مرتب‌سازی: قدیمی‌ترین بروزرسانی</option>
                        <option value="createdAt_asc">مرتب‌سازی: قدیمی‌ترین</option>
                    </select>
                </div>
                <button id="reset-filters-btn" class="text-sm text-indigo-600 hover:underline">حذف فیلترها</button>
            </div>
        </section>

        <main class="container mx-auto p-4 sm:p-6 lg:p-8 pt-4">
            <div id="dashboardView" class="view-content hidden"></div>
            <div id="ordersView" class="view-content hidden"></div>
            <div id="projectsView" class="view-content hidden"></div>
            <div id="quotingView" class="view-content hidden"></div>
            <div id="shippingView" class="view-content hidden"></div>
            <div id="accountingView" class="view-content hidden"></div>
            <div id="archiveView" class="view-content hidden"></div>
            <div id="settingsView" class="view-content hidden"></div>
        </main>
    </div>

    <div id="modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 hidden modal-backdrop opacity-0">
        <div id="modal-content" class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl modal-content transform scale-95"></div>
    </div>
    <div id="toast" class="fixed top-5 right-5 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full z-[101] toast"></div>
    <div id="loader-container" class="fixed inset-0 bg-white/80 z-[200] flex flex-col items-center justify-center hidden">
        <div class="spinner"></div>
        <p class="mt-4 text-gray-600">در حال پردازش...</p>
    </div>

    <script type="module">
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
        import {
            getAuth,
            onAuthStateChanged,
            signInWithEmailAndPassword,
            signOut
        } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
        import {
            getFirestore,
            doc,
            collection,
            query,
            onSnapshot,
            addDoc,
            setDoc,
            updateDoc,
            deleteDoc,
            serverTimestamp,
            getDoc,
            writeBatch,
            where,
            collectionGroup,
            runTransaction,
            arrayUnion
        } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

        let db, auth, currentUser;

        // --- CONFIG & STATE ---
        const config = {
            statuses: {
                'new': {
                    title: 'سفارش جدید',
                    icon: 'fa-plus-circle',
                    color: 'blue-500',
                    progress: 10
                },
                'production': {
                    title: 'در حال ساخت',
                    icon: 'fa-cogs',
                    color: 'orange-500',
                    progress: 40
                },
                'qa': {
                    title: 'کنترل کیفیت',
                    icon: 'fa-check-double',
                    color: 'purple-500',
                    progress: 70
                },
                'completed': {
                    title: 'تکمیل شده',
                    icon: 'fa-box',
                    color: 'green-600',
                    progress: 90
                }
            },
            shippingStatuses: {
                'shipped_internal': {
                    title: 'حمل داخلی',
                    icon: 'fa-truck',
                    color: 'purple-600',
                    progress: 95
                },
                'shipped_main': {
                    title: 'حمل اصلی',
                    icon: 'fa-shipping-fast',
                    color: 'teal-600',
                    progress: 98
                },
                'delivered': {
                    title: 'تحویل مشتری',
                    icon: 'fa-box-check',
                    color: 'emerald-600',
                    progress: 100
                }
            },
            statusOrder: ['new', 'production', 'qa', 'completed']
        };

        const appState = {
            listeners: [],
            currentView: 'dashboardView',
            settingsSubView: 'customers',
            shippingSubView: 'ready',
            accountingSubView: 'customer',
            activeFilters: {
                customerId: '',
                producerId: '',
                projectStatus: '',
                archiveType: '',
                sortOrder: 'updatedAt_desc'
            },
            users: [],
            customers: [],
            producers: [],
            forwarders: [],
            masterProjects: [],
            orders: [],
            quotationRequests: [],
            preForwardingShipments: [],
            mainShipments: [],
            transactions: []
        };

        const DOM = {
            appContainer: document.getElementById('appContainer'),
            loader: document.getElementById('loader-container'),
            currentUser: document.getElementById('currentUser'),
            mainActionButtons: document.getElementById('main-action-buttons'),
            modal: document.getElementById('modal'),
            modalContent: document.getElementById('modal-content'),
            toast: document.getElementById('toast'),
            customerFilter: document.getElementById('customer-filter'),
            producerFilter: document.getElementById('producer-filter'),
            sortOrder: document.getElementById('sort-order'),
            resetFiltersBtn: document.getElementById('reset-filters-btn'),
            globalFilters: document.getElementById('global-filters'),
            archiveTypeFilter: document.getElementById('archive-type-filter')
        };
        window.appState = appState; // For debugging

        // --- UTILITIES ---
        const formatDateTime = (timestamp) => {
            if (!timestamp || !timestamp.seconds) return 'نامشخص';
            return new Date(timestamp.seconds * 1000).toLocaleDateString('fa-IR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };
        const formatDate = (timestamp) => {
            if (!timestamp) return 'نامشخص';
            const date = timestamp.seconds ? new Date(timestamp.seconds * 1000) : new Date(timestamp);
            return date.toLocaleDateString('fa-IR');
        };

        // --- FIREBASE & AUTH ---
        function initializeFirebase() {
            try {
                const firebaseConfig = {
                    apiKey: "AIzaSyDUPBombHPMqw2t55oM_3tFvCpGxpDUKcY",
                    authDomain: "my-mrp-system.firebaseapp.com",
                    projectId: "my-mrp-system",
                    storageBucket: "my-mrp-system.appspot.com",
                    messagingSenderId: "357993535286",
                    appId: "1:357993535286:web:f68a3736603cba05874597"
                };
                const app = initializeApp(firebaseConfig);
                auth = getAuth(app);
                db = getFirestore(app);
                setupApplication();
            } catch (error) {
                console.error('Firebase initialization error:', error);
                document.body.innerHTML = `<div class="p-8 text-center text-red-500">خطا در اتصال به Firebase: ${error.message}</div>`;
            }
        }

        async function setupApplication() {
            currentUser = {
                uid: 'dev-user-01',
                name: 'کاربر اصلی'
            };
            DOM.currentUser.textContent = `کاربر: ${currentUser.name}`;

            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !DOM.modal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            await setupRealtimeListeners();
            switchView('dashboardView');
        }

        function getAuditData(isNew = true) {
            const data = {
                updatedAt: serverTimestamp(),
                updatedBy: {
                    id: currentUser.uid,
                    name: currentUser.name
                }
            };
            if (isNew) {
                data.createdAt = serverTimestamp();
                data.createdBy = {
                    id: currentUser.uid,
                    name: currentUser.name
                };
            }
            return data;
        }

        function setupRealtimeListeners() {
            if (appState.listeners.length > 0) {
                appState.listeners.forEach(unsub => unsub());
                appState.listeners = [];
            }

            const collections = {
                users: "users",
                customers: "customers",
                producers: "producers",
                forwarders: "forwarders",
                masterProjects: "masterProjects",
                orders: "orders",
                quotationRequests: "quotationRequests",
                preForwardingShipments: "preForwardingShipments",
                mainShipments: "mainShipments",
                transactions: "transactions"
            };

            showLoader('در حال همگام‌سازی اطلاعات...');
            const promises = Object.keys(collections).map(key => {
                return new Promise((resolve, reject) => {
                    const q = query(collection(db, collections[key]));
                    const unsubscribe = onSnapshot(q, (snapshot) => {
                        appState[key] = snapshot.docs.map(d => ({
                            id: d.id,
                            ...d.data()
                        }));
                        if (key === 'customers') populateFilterDropdown(DOM.customerFilter, appState.customers, 'مشتریان');
                        if (key === 'producers') populateFilterDropdown(DOM.producerFilter, appState.producers, 'سازندگان');
                        if (appState.currentView) renderCurrentView();
                        resolve();
                    }, (error) => {
                        console.error(`Error listening to ${key}:`, error);
                        reject(error);
                    });
                    appState.listeners.push(unsubscribe);
                });
            });
            return Promise.all(promises).finally(hideLoader);
        }

        // --- UI & VIEW ROUTING ---
        document.querySelectorAll('.view-btn').forEach(btn => btn.addEventListener('click', () => switchView(btn.dataset.view)));

        function switchView(viewId) {
            appState.currentView = viewId;
            document.querySelectorAll('.view-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.view === viewId));
            document.querySelectorAll('.view-content').forEach(view => view.classList.toggle('hidden', view.id !== viewId));

            const viewsWithFilters = ['ordersView', 'projectsView', 'quotingView', 'shippingView', 'archiveView', 'accountingView', 'settingsView'];
            DOM.globalFilters.classList.toggle('hidden', !viewsWithFilters.includes(viewId));

            document.getElementById('project-status-filter-container').classList.toggle('hidden', viewId !== 'projectsView');
            document.getElementById('archive-type-filter-container').classList.toggle('hidden', viewId !== 'archiveView');

            updateActionButtons(viewId);
            renderCurrentView();
        }

        function updateActionButtons(viewId) {
            DOM.mainActionButtons.innerHTML = '';
            let buttonHtml = '';
            if (viewId === 'projectsView') {
                buttonHtml = `<button id="addProjectBtn" class="bg-indigo-600 text-white px-4 py-2 rounded-lg shadow hover:bg-indigo-700 flex items-center"><i class="fas fa-plus ml-2"></i>پروژه جدید</button>`;
            } else if (viewId === 'settingsView') {
                buttonHtml = `<button id="addItemBtn" class="bg-indigo-600 text-white px-4 py-2 rounded-lg shadow hover:bg-indigo-700 flex items-center"><i class="fas fa-plus ml-2"></i>افزودن آیتم</button>`;
            } else if (viewId === 'accountingView') {
                buttonHtml = `<button id="addTransactionBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg shadow hover:bg-green-700 flex items-center"><i class="fas fa-plus ml-2"></i>ثبت تراکنش جدید</button>`;
            }
            DOM.mainActionButtons.innerHTML = buttonHtml;

            if (document.getElementById('addProjectBtn')) document.getElementById('addProjectBtn').addEventListener('click', () => window.openAddEditModal('masterProjects'));
            if (document.getElementById('addItemBtn')) document.getElementById('addItemBtn').addEventListener('click', () => window.openAddEditModal(appState.settingsSubView));
            if (document.getElementById('addTransactionBtn')) document.getElementById('addTransactionBtn').addEventListener('click', () => window.openTransactionModal());
        }

        function renderCurrentView() {
            const viewContainer = document.getElementById(appState.currentView);
            if (!viewContainer) return;

            const renderMap = {
                dashboardView: window.renderDashboardView,
                ordersView: window.renderOrdersView,
                projectsView: window.renderProjectsView,
                quotingView: window.renderQuotingView,
                shippingView: window.renderShippingView,
                accountingView: window.renderAccountingView,
                archiveView: window.renderArchiveView,
                settingsView: window.renderSettingsView,
            };
            if (renderMap[appState.currentView]) {
                renderMap[appState.currentView](viewContainer);
            }
        }

        // --- FILTERING & SORTING ---
        function populateFilterDropdown(select, items, placeholder) {
            const currentVal = select.value;
            select.innerHTML = `<option value="">همه ${placeholder}</option>`;
            items.sort((a, b) => a.name.localeCompare(b.name)).forEach(item => select.innerHTML += `<option value="${item.id}">${item.name}</option>`);
            select.value = currentVal;
        }

        DOM.customerFilter.addEventListener('change', (e) => {
            appState.activeFilters.customerId = e.target.value;
            renderCurrentView();
        });
        DOM.producerFilter.addEventListener('change', (e) => {
            appState.activeFilters.producerId = e.target.value;
            renderCurrentView();
        });
        DOM.sortOrder.addEventListener('change', (e) => {
            appState.activeFilters.sortOrder = e.target.value;
            renderCurrentView();
        });
        DOM.archiveTypeFilter.addEventListener('change', (e) => {
            appState.activeFilters.archiveType = e.target.value;
            renderCurrentView();
        });
        document.getElementById('project-status-filter').addEventListener('change', (e) => {
            appState.activeFilters.projectStatus = e.target.value;
            renderCurrentView();
        });
        DOM.resetFiltersBtn.addEventListener('click', () => {
            appState.activeFilters = {
                customerId: '',
                producerId: '',
                projectStatus: '',
                archiveType: '',
                sortOrder: 'updatedAt_desc'
            };
            DOM.customerFilter.value = '';
            DOM.producerFilter.value = '';
            DOM.sortOrder.value = 'updatedAt_desc';
            document.getElementById('project-status-filter').value = '';
            DOM.archiveTypeFilter.value = '';
            renderCurrentView();
        });

        function applyFiltersAndSort(items, itemType = null) {
            const {
                customerId,
                producerId,
                sortOrder,
                archiveType
            } = appState.activeFilters;
            let filteredItems = items;

            if (itemType === 'archive' && archiveType) {
                filteredItems = filteredItems.filter(item => item.type === archiveType);
            }

            if (customerId || producerId) {
                filteredItems = filteredItems.filter(item => {
                    let customerMatch = !customerId;
                    if (customerId) {
                        if (item.customerId === customerId) customerMatch = true;
                        else if (item.projectId) {
                            const project = appState.masterProjects.find(p => p.id === item.projectId);
                            if (project?.customerId === customerId) customerMatch = true;
                        } else if (item.masterProjectId) {
                            const project = appState.masterProjects.find(p => p.id === item.masterProjectId);
                            if (project?.customerId === customerId) customerMatch = true;
                        } else if (item.orderIds?.length > 0) {
                            const order = appState.orders.find(o => item.orderIds.includes(o.id));
                            const project = appState.masterProjects.find(p => p.id === order?.masterProjectId);
                            if (project?.customerId === customerId) customerMatch = true;
                        } else if (item.party?.type === 'customer' && item.party.id === customerId) {
                            customerMatch = true;
                        }
                    }

                    let producerMatch = !producerId;
                    if (producerId) {
                        if (item.producerId === producerId) producerMatch = true;
                        else if (item.masterProjectId) {
                            if (item.producerId === producerId) producerMatch = true;
                        } else if (item.pieces) {
                            if (appState.orders.some(o => o.masterProjectId === item.id && o.producerId === producerId)) {
                                producerMatch = true;
                            }
                        } else if (item.suppliers) {
                            if (item.suppliers.some(s => s.producerId === producerId)) producerMatch = true;
                        } else if (item.orderIds?.length > 0) {
                            const order = appState.orders.find(o => item.orderIds.includes(o.id));
                            if (order?.producerId === producerId) producerMatch = true;
                        } else if (item.party?.type === 'producer' && item.party.id === producerId) {
                            producerMatch = true;
                        }
                    }

                    return customerMatch && producerMatch;
                });
            }

            const [sortField, sortDir] = sortOrder.split('_');
            filteredItems.sort((a, b) => {
                const timeA = a[sortField]?.seconds || 0;
                const timeB = b[sortField]?.seconds || 0;
                return sortDir === 'desc' ? timeB - timeA : timeA - timeB;
            });

            return filteredItems;
        }

        // --- RENDER FUNCTIONS (Global Scope) ---

        window.renderDashboardView = function(container) {
            const activeOrders = appState.orders.filter(o => !o.isArchived);
            const activeProjects = appState.masterProjects.filter(p => !p.isArchived);
            const pendingQuotes = appState.quotationRequests.filter(q => q.status === 'pending' && !q.isArchived);

            const statCard = (title, value, icon, color) => `
            <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4 space-x-reverse">
                <div class="bg-${color}-100 p-4 rounded-full"><i class="fas ${icon} fa-2x text-${color}-600"></i></div>
                <div><p class="text-gray-500 text-sm">${title}</p><p class="text-3xl font-bold">${value}</p></div>
            </div>`;

            container.innerHTML = `
            <h2 class="text-3xl font-bold mb-6 text-gray-800">داشبورد مدیریتی</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                ${statCard('پروژه‌های فعال', activeProjects.length, 'fa-sitemap', 'purple')}
                ${statCard('سفارشات فعال', activeOrders.length, 'fa-cogs', 'orange')}
                ${statCard('درخواست قیمت باز', pendingQuotes.length, 'fa-comments-dollar', 'cyan')}
                ${statCard('حمل در جریان', appState.mainShipments.filter(s => s.status !== 'delivered' && !s.isArchived).length, 'fa-shipping-fast', 'teal')}
            </div>`;
        }

        window.renderOrdersView = function(container) {
            container.innerHTML = `<div id="kanbanBoard" class="flex overflow-x-auto space-x-reverse space-x-4 pb-4"></div>`;
            const board = container.querySelector('#kanbanBoard');
            const visibleOrders = applyFiltersAndSort(appState.orders.filter(o => !o.isArchived && !o.preForwardingShipmentId));

            config.statusOrder.forEach(statusKey => {
                const statusInfo = config.statuses[statusKey];
                const column = document.createElement('div');
                column.className = 'kanban-column flex-shrink-0 bg-slate-200/60 rounded-xl';
                column.dataset.status = statusKey;
                const ordersInColumn = visibleOrders.filter(o => o.status === statusKey);

                column.innerHTML = `
                <div class="kanban-column-header flex items-center justify-between p-3" style="border-color:${statusInfo.color.replace('-500','').replace('-600','')}">
                    <h3 class="font-bold text-gray-700">${statusInfo.title}</h3>
                    <span class="bg-gray-300 text-gray-800 text-xs font-semibold px-2.5 py-1 rounded-full">${ordersInColumn.length}</span>
                </div>
                <div class="order-list p-2">${ordersInColumn.map(createOrderCard).join('') || '<p class="text-center text-gray-500 mt-4 text-sm">سفارشی نیست.</p>'}</div>`;
                board.appendChild(column);
            });
            setupDragAndDrop();
        }

        function createOrderCard(order) {
            const project = appState.masterProjects.find(p => p.id === order.masterProjectId);
            let archiveButton = '';
            if (!project) {
                archiveButton = `<button class="text-xs text-red-500 hover:underline" onclick="event.stopPropagation(); handleArchiveOrphanOrder('${order.id}')">بایگانی</button>`;
            }
            return `<div class="order-card card" draggable="true" data-id="${order.id}" style="border-right-color: ${config.statuses[order.status]?.color.replace('-500','').replace('-600','')}">
            <div class="flex justify-between items-start">
                <h4 class="font-bold text-base mb-1">${project?.code || order.orderCode || 'سفارش تکی'}</h4>
                ${archiveButton}
            </div>
            <p class="text-sm text-gray-600">قطعات: ${order.pieces?.join(', ')}</p>
            <p class="text-xs text-gray-400 mt-2">سازنده: ${appState.producers.find(p => p.id === order.producerId)?.name || 'نامشخص'}</p>
        </div>`;
        }

        async function handleArchiveOrphanOrder(orderId) {
            showConfirmModal('این سفارش به هیچ پروژه‌ای متصل نیست. آیا می‌خواهید آن را بایگانی کنید؟', async () => {
                showLoader();
                try {
                    await updateDoc(doc(db, "orders", orderId), {
                        isArchived: true,
                        archiveReason: 'سفارش بدون پروژه'
                    });
                    showToast('سفارش بایگانی شد.');
                } catch (err) {
                    showToast('خطا در بایگانی.', 'error');
                } finally {
                    hideLoader();
                }
            });
        }

        function setupDragAndDrop() {
            document.querySelectorAll('.order-card').forEach(card => {
                card.addEventListener('dragstart', () => card.classList.add('dragging'));
                card.addEventListener('dragend', () => card.classList.remove('dragging'));
                card.addEventListener('click', () => window.openOrderModal(card.dataset.id));
            });
            document.querySelectorAll('.order-list').forEach(list => {
                list.addEventListener('dragover', e => {
                    e.preventDefault();
                    list.classList.add('drag-over');
                });
                list.addEventListener('dragleave', () => list.classList.remove('drag-over'));
                list.addEventListener('drop', async e => {
                    e.preventDefault();
                    list.classList.remove('drag-over');
                    const draggingCard = document.querySelector('.dragging');
                    if (!draggingCard) return;

                    const orderId = draggingCard.dataset.id;
                    const newStatus = list.parentElement.dataset.status;
                    const updateData = {
                        status: newStatus,
                        ...getAuditData(false),
                        statusHistory: arrayUnion({
                            status: newStatus,
                            date: new Date()
                        })
                    };

                    if (newStatus === 'completed') {
                        updateData.completedAt = serverTimestamp();
                    }

                    try {
                        await updateDoc(doc(db, "orders", orderId), updateData);
                        showToast('وضعیت سفارش به‌روزرسانی شد.');
                    } catch (err) {
                        showToast('خطا در به‌روزرسانی وضعیت.', 'error');
                    }
                });
            });
        }

        // --- PART 2: CONTINUATION OF SCRIPT ---

        window.renderProjectsView = function(container) {
            let visibleProjects = appState.masterProjects.filter(p => !p.isArchived);
            const {
                projectStatus
            } = appState.activeFilters;

            if (projectStatus) {
                visibleProjects = visibleProjects.filter(p => {
                    const progress = calculateProjectProgress(p);
                    if (projectStatus === 'completed') return progress >= 100;
                    if (projectStatus === 'active') return progress < 100;
                    return true;
                });
            }

            visibleProjects = applyFiltersAndSort(visibleProjects);

            let content = visibleProjects.map(p => {
                const progress = calculateProjectProgress(p);
                const customer = appState.customers.find(c => c.id === p.customerId);
                return `<div class="project-card card cursor-pointer" onclick="window.showProjectDetails('${p.id}')">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-xl font-bold text-gray-800">${customer?.name || 'مشتری نامشخص'}</h3>
                    <span class="font-mono text-sm bg-purple-200 text-purple-800 px-3 py-1 rounded-full">${p.code}</span>
                </div>
                <p class="text-gray-500 text-sm mb-4">تاریخ نیاز: ${formatDate(p.desiredDate)}</p>
                <div class="flex justify-between items-center mb-1 text-sm">
                    <span class="font-semibold text-gray-600">پیشرفت کلی</span>
                    <span>${Math.round(progress)}%</span>
                </div>
                <div class="progress-bar"><div class="progress-bar-fill" style="width: ${progress}%;"></div></div>
            </div>`;
            }).join('');
            container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">${content || '<p class="col-span-full text-center text-gray-500 mt-4">پروژه‌ای یافت نشد.</p>'}</div>`;
        }

        function calculateProjectProgress(project) {
            const projectOrders = appState.orders.filter(o => o.masterProjectId === project.id);
            const activePieces = (project.pieces || []).filter(p => !p.isArchived);
            const totalPieces = activePieces.length;
            if (totalPieces === 0) return 100;

            let totalProgress = 0;
            activePieces.forEach(piece => {
                const order = projectOrders.find(o => o.pieces.includes(piece.name));
                if (order) {
                    if (order.isDelivered) totalProgress += config.shippingStatuses.delivered.progress;
                    else if (order.mainShipmentId) totalProgress += config.shippingStatuses.shipped_main.progress;
                    else if (order.preForwardingShipmentId) totalProgress += config.shippingStatuses.shipped_internal.progress;
                    else totalProgress += config.statuses[order.status]?.progress || 0;
                }
            });
            return totalProgress / totalPieces;
        }

        window.renderQuotingView = function(container) {
            const pendingRequests = applyFiltersAndSort(appState.quotationRequests.filter(q => q.status === 'pending' && !q.isArchived));
            let content = pendingRequests.map(req => {
                const project = appState.masterProjects.find(p => p.id === req.projectId);
                const customer = appState.customers.find(c => c.id === project?.customerId);
                return `<div class="card cursor-pointer" onclick="window.openQuotationDetailsModal('${req.id}')">
                <h4 class="font-bold text-lg">پروژه: ${project?.code} (مشتری: ${customer?.name})</h4>
                <p class="text-sm text-gray-600 mt-2"><b>قطعات جهت قیمت‌گیری:</b> ${(req.pieceNames || []).join(', ')}</p>
            </div>`;
            }).join('');
            container.innerHTML = `
            <h2 class="text-2xl font-bold mb-4">درخواست‌های قیمت در جریان</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">${content || '<p class="col-span-full text-center text-gray-500 mt-6">هیچ درخواست قیمت فعالی وجود ندارد.</p>'}</div>`;
        }

        window.renderShippingView = function(container) {
            const {
                shippingSubView
            } = appState;
            const subViewButtons = `<div class="mb-6 flex border-b bg-white rounded-t-lg p-1">
            <button data-subview="ready" class="tab-button flex-1 ${shippingSubView === 'ready' ? 'active' : ''}">آماده ارسال</button>
            <button data-subview="internal" class="tab-button flex-1 ${shippingSubView === 'internal' ? 'active' : ''}">حمل داخلی</button>
            <button data-subview="main" class="tab-button flex-1 ${shippingSubView === 'main' ? 'active' : ''}">حمل اصلی</button>
        </div>`;
            container.innerHTML = subViewButtons + `<div id="shipping-content" class="bg-white p-6 rounded-b-lg shadow"></div>`;

            container.querySelectorAll('[data-subview]').forEach(btn => btn.addEventListener('click', (e) => {
                appState.shippingSubView = e.target.dataset.subview;
                renderShippingView(container);
            }));

            const contentContainer = container.querySelector('#shipping-content');
            if (shippingSubView === 'ready') renderReadyForShipment(contentContainer);
            if (shippingSubView === 'internal') renderInternalShipments(contentContainer);
            if (shippingSubView === 'main') renderMainShipments(contentContainer);
        }

        function renderReadyForShipment(container) {
            const readyOrders = applyFiltersAndSort(appState.orders.filter(o => o.status === 'completed' && !o.preForwardingShipmentId && !o.isArchived));
            const rows = readyOrders.map(order => {
                const project = appState.masterProjects.find(p => p.id === order.masterProjectId);
                const customer = appState.customers.find(c => c.id === project?.customerId);
                const producer = appState.producers.find(p => p.id === order.producerId);
                return `
            <tr class="border-b">
                <td class="p-3"><input type="checkbox" class="order-checkbox" data-id="${order.id}"></td>
                <td class="p-3 font-semibold">${project?.code || 'N/A'}</td>
                <td class="p-3">${customer?.name || 'N/A'}</td>
                <td class="p-3">${order.pieces?.join(', ')}</td>
                <td class="p-3">${producer?.name || 'N/A'}</td>
                <td class="p-3">${producer?.city || 'N/A'}</td>
                <td class="p-3">${formatDateTime(order.completedAt)}</td>
            </tr>`;
            }).join('');
            container.innerHTML = `
            <h3 class="text-xl font-bold mb-4">سفارشات تکمیل‌شده و آماده ارسال</h3>
            <div class="overflow-x-auto"><table class="w-full text-sm text-right">
                <thead class="bg-gray-50"><tr>
                    <th class="p-3 w-12"><input type="checkbox" id="select-all-orders"></th>
                    <th class="p-3">پروژه</th>
                    <th class="p-3">مشتری</th>
                    <th class="p-3">قطعات</th>
                    <th class="p-3">سازنده</th>
                    <th class="p-3">شهر</th>
                    <th class="p-3">تاریخ تکمیل</th>
                </tr></thead>
                <tbody>${rows || `<tr><td colspan="7" class="text-center p-4">سفارش آماده‌ای یافت نشد.</td></tr>`}</tbody>
            </table></div>
            ${readyOrders.length > 0 ? `<button onclick="handleCreatePreShipment()" class="mt-4 bg-purple-600 text-white px-4 py-2 rounded-lg">ایجاد بسته حمل داخلی</button>` : ''}`;
            container.querySelector('#select-all-orders')?.addEventListener('change', (e) => container.querySelectorAll('.order-checkbox').forEach(chk => chk.checked = e.target.checked));
        }

        function renderInternalShipments(container) {
            const internalShipments = applyFiltersAndSort(appState.preForwardingShipments.filter(s => !s.mainShipmentId && !s.isArchived));
            const rows = internalShipments.map(s => {
                const forwarder = appState.forwarders.find(f => f.id === s.forwarderId);
                const allReceived = s.orderIds.length === (s.receivedOrderIds?.length || 0);
                const statusText = allReceived ? 'رسیده به فورواردر' : 'در حال ارسال';
                const statusColor = allReceived ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800';

                return `<tr class="border-b cursor-pointer hover:bg-gray-50" onclick="window.showPreShipmentDetails('${s.id}')">
                <td class="p-3"><input type="checkbox" class="shipment-checkbox" data-id="${s.id}" ${allReceived ? '' : 'disabled'} onclick="(e) => e.stopPropagation()"></td>
                <td class="p-3 font-mono">${s.trackingNumber}</td>
                <td class="p-3">${forwarder?.name || 'نامشخص'}</td>
                <td class="p-3">${s.orderIds?.length || 0}</td>
                <td class="p-3"><span class="px-2 py-1 text-xs rounded-full ${statusColor}">${statusText}</span></td>
            </tr>`;
            }).join('');
            container.innerHTML = `
            <h3 class="text-xl font-bold mb-4">بسته‌های در حال حمل داخلی</h3>
            <table class="w-full text-sm text-right"><thead class="bg-gray-50"><tr>
                <th class="p-3 w-12"><input type="checkbox" id="select-all-shipments"></th>
                <th class="p-3">شماره رهگیری</th>
                <th class="p-3">فورواردر</th>
                <th class="p-3">تعداد سفارش</th>
                <th class="p-3">وضعیت</th>
            </tr></thead>
            <tbody>${rows || `<tr><td colspan="5" class="text-center p-4">بسته‌ای یافت نشد.</td></tr>`}</tbody></table>
            ${internalShipments.length > 0 ? `<button onclick="handleCreateMainShipment()" class="mt-4 bg-teal-600 text-white px-4 py-2 rounded-lg">ایجاد محموله اصلی</button>` : ''}`;
            container.querySelector('#select-all-shipments')?.addEventListener('change', (e) => {
                container.querySelectorAll('.shipment-checkbox:not(:disabled)').forEach(chk => chk.checked = e.target.checked)
            });
        }

        function renderMainShipments(container) {
            const rows = applyFiltersAndSort(appState.mainShipments.filter(s => !s.isArchived)).map(shipment => {
                const forwarder = appState.forwarders.find(f => f.id === shipment.forwarderId);
                return `<tr class="border-b cursor-pointer hover:bg-gray-50" onclick="window.showMainShipmentDetails('${shipment.id}')">
                <td class="p-3 font-mono">${shipment.forwarderRef}</td>
                <td class="p-3">${forwarder?.name || 'N/A'}</td>
                <td class="p-3">${shipment.shippingMethod || 'N/A'}</td>
                <td class="p-3">${shipment.preShipmentIds?.length || 0}</td>
                <td class="p-3">${shipment.status === 'delivered' ? 'تحویل شده' : 'در حال حمل'}</td>
            </tr>`
            }).join('');
            container.innerHTML = `
             <h3 class="text-xl font-bold mb-4">محموله‌های اصلی</h3><table class="w-full text-sm text-right">
                 <thead class="bg-gray-50"><tr>
                     <th class="p-3">کد مرجع</th>
                     <th class="p-3">فورواردر</th>
                     <th class="p-3">نوع حمل</th>
                     <th class="p-3">تعداد بسته‌ها</th>
                     <th class="p-3">وضعیت</th>
                 </tr></thead>
                 <tbody>${rows || `<tr><td colspan="5" class="text-center p-4">محموله‌ای یافت نشد.</td></tr>`}</tbody></table>`;
        }

        window.renderAccountingView = function(container) {
            const {
                accountingSubView
            } = appState;
            const subViewButtons = `<div class="mb-6 flex border-b bg-white rounded-t-lg p-1">
            <button data-subview="customer" class="tab-button flex-1 ${accountingSubView === 'customer' ? 'active' : ''}">حساب مشتریان</button>
            <button data-subview="producer" class="tab-button flex-1 ${accountingSubView === 'producer' ? 'active' : ''}">حساب سازندگان</button>
        </div>`;
            container.innerHTML = subViewButtons + `<div id="accounting-content" class="bg-white p-6 rounded-b-lg shadow"></div>`;
            container.querySelectorAll('[data-subview]').forEach(btn => btn.addEventListener('click', (e) => {
                appState.accountingSubView = e.target.dataset.subview;
                renderAccountingView(container);
            }));

            const contentContainer = container.querySelector('#accounting-content');
            if (accountingSubView === 'customer') {
                const customersWithBalance = appState.customers.map(c => {
                    const transactions = appState.transactions.filter(t => t.party.type === 'customer' && t.party.id === c.id);
                    const balance = transactions.reduce((acc, t) => acc + (parseFloat(t.amount) || 0), 0);
                    const lastTx = transactions.sort((a, b) => (b.createdAt?.seconds || 0) - (a.createdAt?.seconds || 0))[0];
                    return {
                        ...c,
                        balance,
                        updatedAt: lastTx?.createdAt || c.updatedAt,
                        createdAt: c.createdAt,
                        customerId: c.id
                    };
                });
                let rows = applyFiltersAndSort(customersWithBalance).map(c => `
                 <tr class="border-b"><td class="p-3">${c.name}</td><td class="p-3 font-mono">${c.balance.toLocaleString()}</td>
                 <td class="p-3"><button class="text-indigo-600 text-sm hover:underline" onclick="alert('Not implemented')">مشاهده صورتحساب</button></td></tr>`).join('');
                contentContainer.innerHTML = `
                <h3 class="text-xl font-bold mb-4">مانده حساب مشتریان</h3><table class="w-full text-sm text-right">
                <thead class="bg-gray-50"><tr><th class="p-3">نام مشتری</th><th class="p-3">جمع پرداختی</th><th class="p-3">عملیات</th></tr></thead>
                <tbody>${rows || '<tr><td colspan="3" class="p-4 text-center">مشتری یافت نشد.</td></tr>'}</tbody></table>`;
            } else { // Producers
                contentContainer.innerHTML = `<p>بخش حساب سازندگان در حال توسعه است.</p>`
            }
        }

        window.renderArchiveView = function(container) {
            let allArchivedItems = [
                ...appState.masterProjects.filter(i => i.isArchived).map(i => ({
                    ...i,
                    type: 'masterProjects'
                })),
                ...appState.orders.filter(i => i.isArchived).map(i => ({
                    ...i,
                    type: 'orders'
                })),
                ...appState.quotationRequests.filter(i => i.isArchived).map(i => ({
                    ...i,
                    type: 'quotationRequests'
                })),
                ...appState.mainShipments.filter(i => i.isArchived).map(i => ({
                    ...i,
                    type: 'mainShipments'
                }))
            ];

            const visibleItems = applyFiltersAndSort(allArchivedItems, 'archive');

            let content = visibleItems.map(item => {
                let title, code, id;
                if (item.type === 'orders') {
                    title = `سفارش: ${(item.pieces || []).join(', ')}`;
                    code = item.orderCode;
                    id = item.id;
                } else if (item.type === 'quotationRequests') {
                    const project = appState.masterProjects.find(p => p.id === item.projectId);
                    title = `درخواست قیمت: ${project?.code || 'نامشخص'}`;
                    code = `قطعات: ${(item.pieceNames || []).join(', ')}`;
                    id = item.id;
                } else if (item.type === 'mainShipments') {
                    title = `محموله اصلی: ${item.forwarderRef}`;
                    code = `نوع حمل: ${item.shippingMethod}`;
                    id = item.id;
                } else { // masterProjects
                    title = `پروژه: ${item.code}`;
                    code = item.code;
                    id = item.id;
                }

                return `<div class="card flex justify-between items-center cursor-pointer" onclick="showArchivedItemDetails('${item.type}', '${id}')">
                <div>
                    <h4 class="font-bold">${title} <span class="font-mono text-sm bg-gray-200 p-1 rounded">${code}</span></h4>
                    <p class="text-sm mt-2 text-red-600"><b>علت بایگانی:</b> ${item.archiveReason || 'نامشخص'}</p>
                </div>
                <div class="flex items-center gap-4">
                    <button class="text-xs text-green-700 hover:underline" onclick="event.stopPropagation(); handleUnarchive('${item.type}', '${id}')">خروج از بایگانی</button>
                    <button class="text-xs text-red-700 hover:underline" onclick="event.stopPropagation(); handlePermanentDelete('${item.type}', '${id}')">حذف کامل</button>
                </div>
            </div>`;
            }).join('');

            container.innerHTML = `<h2 class="text-2xl font-bold mb-4">آیتم‌های بایگانی شده</h2><div class="space-y-4">${content || '<p>موردی در بایگانی یافت نشد.</p>'}</div>`;
        }

        window.renderSettingsView = function(container) {
            const {
                settingsSubView
            } = appState;
            const subViewButtons = `<div class="mb-6 flex border-b bg-white rounded-t-lg p-1">
            <button data-subview="customers" class="tab-button flex-1 ${settingsSubView === 'customers' ? 'active' : ''}">مشتریان</button>
            <button data-subview="producers" class="tab-button flex-1 ${settingsSubView === 'producers' ? 'active' : ''}">سازندگان</button>
            <button data-subview="forwarders" class="tab-button flex-1 ${settingsSubView === 'forwarders' ? 'active' : ''}">فورواردرها</button>
        </div>`;
            container.innerHTML = subViewButtons + `<div id="settings-content" class="bg-white p-6 rounded-b-lg shadow"></div>`;
            container.querySelectorAll('[data-subview]').forEach(btn => btn.addEventListener('click', (e) => {
                appState.settingsSubView = e.target.dataset.subview;
                renderSettingsView(container);
                updateActionButtons('settingsView');
            }));

            const data = applyFiltersAndSort(appState[settingsSubView] || []);
            let headers = `<th>نام</th>`;
            if (settingsSubView === 'customers') {
                headers += `<th>کد</th><th>شماره شروع پروژه</th>`;
            } else if (settingsSubView === 'producers') {
                headers += `<th>کد/پیشوند</th><th>شهر</th>`;
            } else { // forwarders
                headers += `<th>کد</th>`;
            }
            headers += `<th>عملیات</th>`;

            const rows = data.map(item => {
                let rowHtml = `<tr class="border-b"><td class="p-3">${item.name}</td>`;
                if (settingsSubView === 'customers') {
                    rowHtml += `<td class="p-3 font-mono">${item.code || ''}</td><td class="p-3 font-mono">${item.projectStartNumber || ''}</td>`;
                } else if (settingsSubView === 'producers') {
                    rowHtml += `<td class="p-3 font-mono">${item.codePrefix || ''}</td><td class="p-3">${item.city || ''}</td>`;
                } else { // forwarders
                    rowHtml += `<td class="p-3 font-mono">${item.code || ''}</td>`;
                }
                rowHtml += `<td class="p-3"><button class="text-blue-600 hover:underline" onclick="window.openAddEditModal('${settingsSubView}', '${item.id}')">ویرایش</button>
            <button class="text-red-600 hover:underline mr-4" onclick="window.handleDelete('${settingsSubView}', '${item.id}')">حذف</button></td></tr>`;
                return rowHtml;
            }).join('');
            container.querySelector('#settings-content').innerHTML = `<table class="w-full text-sm text-right"><thead class="bg-gray-50"><tr>${headers}</tr></thead><tbody>${rows}</tbody></table>`;
        }

        // --- MODALS AND ACTIONS (Global Scope) ---

        window.openOrderModal = function(orderId) {
            const order = appState.orders.find(o => o.id === orderId);
            if (!order) return;

            const tabs = config.statusOrder.map(statusKey =>
                `<button class="tab-button" data-tab="${statusKey}">${config.statuses[statusKey].title}</button>`).join('');

            const modalHtml = `
        <div class="p-6 flex flex-col h-full">
            <button onclick="closeModal()" class="modal-close-btn">&times;</button>
            <h2 class="text-2xl font-bold">جزئیات سفارش: ${order.orderCode}</h2>
            <div class="border-b border-gray-200 mt-4"><nav class="flex space-x-reverse space-x-4">${tabs}</nav></div>
            <form id="order-details-form" class="flex-grow overflow-y-auto py-4"><div id="order-details-content"></div></form>
            <div class="flex justify-end space-x-reverse space-x-2 pt-4 mt-auto border-t">
                <button type="button" class="px-4 py-2 bg-gray-200 rounded-lg" onclick="closeModal()">بستن</button>
                <button type="button" id="save-order-details" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">ذخیره تغییرات</button>
            </div>
        </div>`;
            openModal(modalHtml, 'max-w-3xl');

            const formContainer = document.getElementById('order-details-content');
            const tabButtons = document.querySelectorAll('.tab-button');

            function renderOrderTab(statusKey) {
                tabButtons.forEach(b => b.classList.toggle('active', b.dataset.tab === statusKey));
                let content = '';
                const details = order.stageDetails?.[statusKey] || {};

                if (statusKey === 'new') content = createFormField('یادداشت‌ها', 'textarea', 'notes', details.notes);
                if (statusKey === 'production') content = createFormField('تاریخ شروع تولید', 'date', 'startDate', details.startDate) + createFormField('یادداشت‌های تولید', 'textarea', 'notes', details.notes);
                if (statusKey === 'qa') content = createFormField('نتایج کنترل کیفیت', 'textarea', 'notes', details.notes);
                if (statusKey === 'completed') content = createFormField('وزن نهایی (Kg)', 'number', 'finalWeight', details.finalWeight) + createFormField('یادداشت‌های تکمیلی', 'textarea', 'notes', details.notes);

                formContainer.innerHTML = content;
            }

            tabButtons.forEach(btn => btn.addEventListener('click', () => renderOrderTab(btn.dataset.tab)));
            renderOrderTab(order.status);

            document.getElementById('save-order-details').addEventListener('click', async () => {
                const form = document.getElementById('order-details-form');
                const formData = new FormData(form);
                const activeTab = document.querySelector('.tab-button.active').dataset.tab;
                let updateData = {
                    ...order.stageDetails
                };
                if (!updateData[activeTab]) updateData[activeTab] = {};
                for (let [key, value] of formData.entries()) {
                    updateData[activeTab][key] = value;
                }

                showLoader();
                try {
                    await updateDoc(doc(db, 'orders', orderId), {
                        stageDetails: updateData,
                        ...getAuditData(false)
                    });
                    showToast('جزئیات سفارش ذخیره شد.');
                    closeModal();
                } catch (e) {
                    showToast('خطا در ذخیره.', 'error');
                } finally {
                    hideLoader();
                }
            });
        }

        function createFormField(label, type, name, value = '') {
            const inputHtml = type === 'textarea' ?
                `<textarea name="${name}" class="mt-1 w-full p-2 border rounded-md h-24">${value || ''}</textarea>` :
                `<input type="${type}" name="${name}" value="${value || ''}" class="mt-1 w-full p-2 border rounded-md">`;
            return `<div class="mb-4"><label class="block text-sm font-medium text-gray-700">${label}</label>${inputHtml}</div>`;
        }

        window.openAddEditModal = function(type, id = null) {
            const isEdit = !!id;
            const item = isEdit ? appState[type].find(i => i.id === id) : null;
            let title, formContent, size = 'max-w-lg';
            let fullModalHtml = '';

            switch (type) {
                case 'customers':
                    title = `${isEdit ? 'ویرایش' : 'افزودن'} مشتری`;
                    formContent = `
                    <div><label class="block mb-1">نام</label><input id="name" type="text" class="w-full p-2 border rounded" value="${item?.name || ''}"></div>
                    <div><label class="block mb-1">کد مشتری</label><input id="code" type="text" class="w-full p-2 border rounded" value="${item?.code || ''}"></div>
                    <div><label class="block mb-1">شماره شروع پروژه</label><input id="projectStartNumber" type="number" class="w-full p-2 border rounded" value="${item?.projectStartNumber || '1'}"></div>`;
                    break;
                case 'producers':
                    title = `${isEdit ? 'ویرایش' : 'افزودن'} سازنده`;
                    formContent = `
                     <div><label class="block mb-1">نام</label><input id="name" type="text" class="w-full p-2 border rounded" value="${item?.name || ''}"></div>
                     <div><label class="block mb-1">کد/پیشوند</label><input id="codePrefix" type="text" class="w-full p-2 border rounded" value="${item?.codePrefix || ''}"></div>
                     <div><label class="block mb-1">شهر</label><input id="city" type="text" class="w-full p-2 border rounded" value="${item?.city || ''}"></div>`;
                    break;
                case 'forwarders':
                    title = `${isEdit ? 'ویرایش' : 'افزودن'} فورواردر`;
                    formContent = `
                     <div><label class="block mb-1">نام</label><input id="name" type="text" class="w-full p-2 border rounded" value="${item?.name || ''}"></div>
                     <div><label class="block mb-1">کد</label><input id="code" type="text" class="w-full p-2 border rounded" value="${item?.code || ''}"></div>`;
                    break;
                case 'masterProjects':
                    size = 'max-w-4xl';
                    title = isEdit ? 'ویرایش پروژه' : 'افزودن پروژه';
                    const customerOpts = appState.customers.map(c => `<option value="${c.id}" ${item?.customerId === c.id ? 'selected':''}>${c.name}</option>`).join('');
                    fullModalHtml = `
                    <div class="p-6 flex flex-col h-full">
                         <button onclick="closeModal()" class="modal-close-btn">&times;</button>
                         <h2 class="text-xl font-bold mb-4 flex-shrink-0">${title}</h2>
                         <form id="genericForm" class="flex-grow overflow-y-auto pr-2 space-y-4">
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                 <div>
                                     <label>مشتری</label>
                                     <select id="customerId" class="w-full p-2 border rounded bg-white" required>${customerOpts}</select>
                                 </div>
                                 <div>
                                     <label>کد پروژه (خودکار)</label>
                                     <input id="code" type="text" class="w-full p-2 border rounded bg-gray-100" readonly value="${item?.code || ''}">
                                 </div>
                                 <div class="md:col-span-2">
                                      <label>تاریخ نیاز مشتری</label>
                                      <div class="flex items-center">
                                         <input type="text" id="desired-date-picker" class="w-full p-2 border rounded bg-white" placeholder="تاریخ را انتخاب کنید">
                                         <input type="hidden" id="gregorian-date-hidden">
                                         <span id="gregorian-date" class="mr-4 text-sm text-gray-500 whitespace-nowrap"></span>
                                      </div>
                                 </div>
                                 <div class="md:col-span-2">
                                     <label>توضیحات</label>
                                     <textarea id="description" class="w-full p-2 border rounded h-20">${item?.description || ''}</textarea>
                                 </div>
                             </div>
                             <div class="border-t pt-4 mt-4">
                                 <div class="flex justify-between items-center mb-2">
                                     <div class="flex items-baseline gap-4">
                                        <h3 class="font-semibold text-lg">قطعه‌ها</h3>
                                        <span class="text-sm text-gray-600">وزن کل: <b id="total-weight" class="font-mono">0</b> Kg</span>
                                     </div>
                                     <div>
                                         <button id="add-dependency-group-btn" type="button" class="text-sm bg-yellow-100 text-yellow-800 px-3 py-1 rounded-md hover:bg-yellow-200"><i class="fas fa-link ml-1"></i>ایجاد گروه همبستگی</button>
                                         <button id="add-piece-btn" type="button" class="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded-md hover:bg-blue-200"><i class="fas fa-plus ml-1"></i>افزودن قطعه</button>
                                     </div>
                                 </div>
                                 ${isEdit ? '<p class="text-xs text-orange-600 mb-2">توجه: امکان ویرایش یا ایجاد گروه‌های همبستگی پس از ثبت اولیه پروژه وجود ندارد.</p>' : ''}
                                 <div id="pieces-container" class="space-y-3 max-h-60 overflow-y-auto p-2 bg-slate-50 rounded-lg"></div>
                             </div>
                         </form>
                         <div class="flex justify-end space-x-reverse space-x-2 pt-4 mt-auto border-t flex-shrink-0">
                             <button type="button" class="px-4 py-2 bg-gray-200 rounded" onclick="closeModal()">انصراف</button>
                             <button type="submit" form="genericForm" class="px-4 py-2 bg-indigo-600 text-white rounded">ذخیره</button>
                         </div>
                    </div>`;
                    break;
                default:
                    return;
            }

            if (type !== 'masterProjects') {
                fullModalHtml = `
                <div class="p-6 flex flex-col h-full">
                    <button onclick="closeModal()" class="modal-close-btn">&times;</button>
                    <h2 class="text-xl font-bold mb-4 flex-shrink-0">${title}</h2>
                    <form id="genericForm" class="flex-grow overflow-y-auto pr-2 space-y-4">
                        ${formContent}
                    </form>
                    <div class="flex justify-end space-x-reverse space-x-2 pt-4 mt-auto border-t flex-shrink-0">
                        <button type="button" class="px-4 py-2 bg-gray-200 rounded" onclick="closeModal()">انصراف</button>
                        <button type="submit" form="genericForm" class="px-4 py-2 bg-indigo-600 text-white rounded">ذخیره</button>
                    </div>
                </div>`;
            }

            openModal(fullModalHtml, size);

            const form = document.getElementById('genericForm');
            if (!form) {
                console.error("Form not found in modal!");
                return;
            }

            if (type === 'masterProjects') {
                const customerSelect = document.getElementById('customerId');
                const codeInput = document.getElementById('code');
                const piecesContainer = document.getElementById('pieces-container');
                let dependencyGroupCounter = 0;

                const updateProjectCode = () => {
                    const selectedCustomerId = customerSelect.value;
                    const customer = appState.customers.find(c => c.id === selectedCustomerId);
                    if (customer && !isEdit) {
                        const nextNumber = (customer.projectStartNumber || 1);
                        codeInput.value = `${customer.code || 'CUST'}-${nextNumber}`;
                    }
                };

                const updateTotals = () => {
                    let totalWeight = 0;
                    document.querySelectorAll('.piece-input-group').forEach(group => {
                        const qty = parseFloat(group.querySelector('.piece-quantity').value) || 0;
                        const weight = parseFloat(group.querySelector('.piece-weight').value) || 0;
                        totalWeight += qty * weight;
                    });
                    document.getElementById('total-weight').textContent = totalWeight.toFixed(2);
                };

                const addPieceInput = (piece = {}) => {
                    const div = document.createElement('div');
                    div.className = 'piece-input-group grid grid-cols-12 gap-x-3 gap-y-2 items-center p-2 bg-white rounded-lg border';
                    if (piece.dependencyGroup) {
                        div.dataset.dependencyGroup = piece.dependencyGroup;
                    }
                    div.innerHTML = `
                    <div class="col-span-12 md:col-span-3"><input type="text" class="piece-name w-full p-1.5 border rounded text-sm" value="${piece.name || ''}" placeholder="نام قطعه" required></div>
                    <div class="col-span-4 md:col-span-1"><input type="number" class="piece-quantity w-full p-1.5 border rounded text-sm" value="${piece.quantity || '1'}" placeholder="تعداد"></div>
                    <div class="col-span-4 md:col-span-1"><input type="number" step="0.01" class="piece-weight w-full p-1.5 border rounded text-sm" value="${piece.weight || ''}" placeholder="وزن"></div>
                    <div class="col-span-4 md:col-span-2"><input type="number" class="piece-target-price w-full p-1.5 border rounded text-sm" value="${piece.targetPrice || ''}" placeholder="قیمت هدف"></div>
                    <div class="col-span-10 md:col-span-4"><input type="file" multiple class="piece-drawing w-full text-xs"></div>
                    <div class="col-span-2 md:col-span-1 flex justify-end items-center">
                       <input type="checkbox" class="dependency-checkbox h-5 w-5" title="انتخاب برای گروه همبستگی" ${isEdit ? 'disabled' : ''}>
                       <button type="button" class="remove-piece-btn text-gray-500 hover:text-red-600 p-1 mr-2" title="حذف قطعه"><i class="fas fa-trash-alt"></i></button>
                    </div>
                `;
                    div.querySelector('.remove-piece-btn').addEventListener('click', () => {
                        div.remove();
                        updateTotals();
                    });
                    div.querySelectorAll('.piece-quantity, .piece-weight').forEach(el => el.addEventListener('input', updateTotals));
                    piecesContainer.appendChild(div);
                    return div;
                };

                const addDepGroupBtn = document.getElementById('add-dependency-group-btn');
                if (isEdit) {
                    addDepGroupBtn.disabled = true;
                    addDepGroupBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    addDepGroupBtn.addEventListener('click', () => {
                        const selectedCheckboxes = piecesContainer.querySelectorAll('.dependency-checkbox:checked');
                        if (selectedCheckboxes.length < 2) {
                            showToast('برای ایجاد گروه، حداقل دو قطعه را انتخاب کنید.', 'error');
                            return;
                        }
                        dependencyGroupCounter = (dependencyGroupCounter % 5) + 1;
                        const groupClass = `dependency-group-${dependencyGroupCounter}`;
                        const groupId = `group-${Date.now()}`;

                        selectedCheckboxes.forEach(cb => {
                            const parentGroup = cb.closest('.piece-input-group');
                            parentGroup.dataset.dependencyGroup = groupId;
                            cb.checked = false;
                        });
                        showToast(`گروه همبستگی با شناسه ${groupId} ایجاد شد.`);
                    });
                }

                document.getElementById('add-piece-btn').addEventListener('click', () => addPieceInput());
                customerSelect.addEventListener('change', updateProjectCode);

                const datePicker = $("#desired-date-picker").persianDatepicker({
                    format: 'YYYY/MM/DD',
                    altField: '#gregorian-date-hidden',
                    altFormat: 'X',
                    observer: true,
                    onSelect: function(unix) {
                        const gregorian = new Date(unix).toLocaleDateString("en-CA");
                        $('#gregorian-date').text(`(${gregorian})`);
                    }
                });

                if (isEdit && item?.desiredDate) {
                    const m = moment(item.desiredDate);
                    datePicker.setDate(m.valueOf());
                }

                if (isEdit && item?.pieces) {
                    item.pieces.forEach(p => addPieceInput(p));
                } else {
                    addPieceInput();
                }
                updateProjectCode();
                updateTotals();
            }

            form.addEventListener('submit', async e => {
                e.preventDefault();
                let data = {};
                try {
                    if (type === 'masterProjects') {
                        const pieceGroups = form.querySelectorAll('.piece-input-group');
                        const pieces = Array.from(pieceGroups).map(group => ({
                            name: group.querySelector('.piece-name').value.trim(),
                            quantity: parseInt(group.querySelector('.piece-quantity').value) || 1,
                            weight: parseFloat(group.querySelector('.piece-weight').value) || 0,
                            targetPrice: parseFloat(group.querySelector('.piece-target-price').value) || 0,
                            dependencyGroup: group.dataset.dependencyGroup || null,
                            drawings: Array.from(group.querySelector('.piece-drawing').files).map(f => f.name),
                            isArchived: false
                        })).filter(p => p.name);

                        if (pieces.length === 0) {
                            showToast('پروژه باید حداقل یک قطعه داشته باشد.', 'error');
                            return;
                        }

                        const gregorianDateTimestamp = document.getElementById('gregorian-date-hidden').value;

                        data = {
                            code: form.querySelector('#code').value,
                            customerId: form.querySelector('#customerId').value,
                            description: form.querySelector('#description').value,
                            desiredDate: gregorianDateTimestamp ? new Date(parseInt(gregorianDateTimestamp, 10) * 1000).toISOString() : null,
                            pieces: pieces,
                            isArchived: false
                        };
                    } else if (type === 'customers') {
                        data = {
                            name: form.querySelector('#name').value,
                            code: form.querySelector('#code').value,
                            projectStartNumber: parseInt(form.querySelector('#projectStartNumber').value) || 1
                        };
                    } else if (type === 'producers') {
                        data = {
                            name: form.querySelector('#name').value,
                            codePrefix: form.querySelector('#codePrefix').value,
                            city: form.querySelector('#city').value
                        };
                    } else { // forwarders
                        data = {
                            name: form.querySelector('#name').value,
                            code: form.querySelector('#code').value
                        };
                    }

                    showLoader();
                    if (isEdit) {
                        await updateDoc(doc(db, type, id), {
                            ...data,
                            ...getAuditData(false)
                        });
                    } else {
                        if (type === 'masterProjects') {
                            const customerRef = doc(db, "customers", data.customerId);
                            await runTransaction(db, async (transaction) => {
                                const customerDoc = await transaction.get(customerRef);
                                if (!customerDoc.exists()) throw "مشتری انتخاب شده معتبر نیست.";

                                const newStartNumber = (customerDoc.data().projectStartNumber || 1) + 1;
                                transaction.update(customerRef, {
                                    projectStartNumber: newStartNumber
                                });

                                const newProjectRef = doc(collection(db, type));
                                transaction.set(newProjectRef, {
                                    ...data,
                                    ...getAuditData(true)
                                });
                            });
                        } else {
                            await addDoc(collection(db, type), {
                                ...data,
                                ...getAuditData(true)
                            });
                        }
                    }
                    showToast('عملیات موفق بود.');
                    closeModal();
                } catch (err) {
                    showToast(`خطا: ${err.message || err}`, 'error');
                    console.error(err);
                } finally {
                    hideLoader();
                }
            });
        }

        window.showProjectDetails = function(projectId) {
            const project = appState.masterProjects.find(p => p.id === projectId);
            if (!project) return;

            const customer = appState.customers.find(c => c.id === project.customerId);
            const totalWeight = (project.pieces || []).reduce((sum, piece) => sum + ((piece.quantity || 0) * (piece.weight || 0)), 0);

            const dependencyGroupColors = {};
            let colorIndex = 1;

            const piecesHtml = (project.pieces || []).map(piece => {
                let depClass = '';
                if (piece.dependencyGroup) {
                    if (!dependencyGroupColors[piece.dependencyGroup]) {
                        dependencyGroupColors[piece.dependencyGroup] = `dependency-group-${colorIndex++}`;
                        if (colorIndex > 5) colorIndex = 1;
                    }
                    depClass = dependencyGroupColors[piece.dependencyGroup];
                }

                return `
            <details class="bg-white rounded-lg border group ${depClass}">
                <summary class="flex justify-between items-center p-3 cursor-pointer list-none">
                    <div class="flex items-center">
                        <input type="checkbox" class="ml-3 h-4 w-4 piece-for-quote-checkbox" data-piece-name="${piece.name}" data-dependency-group="${piece.dependencyGroup || ''}" ${piece.isArchived || appState.orders.some(o => o.masterProjectId === projectId && o.pieces.includes(piece.name)) ? 'disabled' : ''}>
                        <span class="font-semibold text-slate-800">${piece.name}</span>
                        ${piece.dependencyGroup ? '<i class="fas fa-link text-xs text-yellow-500 mr-2" title="عضو گروه همبستگی"></i>' : ''}
                        ${piece.isArchived ? '<span class="text-xs text-red-500 mr-2">(بایگانی شده)</span>' : ''}
                    </div>
                    <i class="fas fa-chevron-down group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="p-4 border-t bg-slate-50/50">
                    ${buildPieceTimeline(piece, project)}
                </div>
            </details>
        `
            }).join('');

            const modalHtml = `
        <div class="p-0 flex flex-col h-full bg-slate-50">
            <div class="p-4 border-b bg-white flex justify-between items-center flex-shrink-0">
                <h2 class="text-xl font-bold">جزئیات پروژه: ${project.code}</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <div class="flex-grow grid grid-cols-12 gap-6 p-4 overflow-y-auto">
                <div class="col-span-12 lg:col-span-8 space-y-3">
                    ${piecesHtml || '<p>هیچ قطعه‌ای برای این پروژه ثبت نشده است.</p>'}
                </div>
                <aside class="col-span-12 lg:col-span-4">
                    <div class="bg-white p-4 rounded-lg shadow-sm border space-y-3 text-sm">
                        <h3 class="font-bold text-lg border-b pb-2 mb-2">اطلاعات پروژه</h3>
                        <p><strong>مشتری:</strong> ${customer?.name || 'نامشخص'}</p>
                        <p><strong>کد پروژه:</strong> <span class="font-mono">${project.code}</span></p>
                        <p><strong>تاریخ ایجاد:</strong> ${formatDateTime(project.createdAt)}</p>
                        <p><strong>تاریخ نیاز:</strong> ${formatDate(project.desiredDate)}</p>
                        <p><strong>وزن کل تخمینی:</strong> ${totalWeight.toFixed(2)} Kg</p>
                        <div><strong>توضیحات:</strong> <p class="text-gray-600 mt-1">${project.description || '-'}</p></div>
                    </div>
                </aside>
            </div>
            <div class="flex justify-between items-center p-4 mt-auto border-t flex-shrink-0 bg-white">
                <div>
                     <button id="editProjectBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg">ویرایش</button>
                     <button id="deleteProjectBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg mr-2">بایگانی</button>
                </div>
                <div class="flex space-x-reverse space-x-2">
                    <button type="button" class="px-4 py-2 bg-gray-200 rounded" onclick="closeModal()">بستن</button>
                    <button id="createQuoteBtn" class="px-4 py-2 bg-cyan-600 text-white rounded">ایجاد درخواست قیمت</button>
                </div>
            </div>
        </div>`;
            openModal(modalHtml, 'max-w-6xl');

            document.querySelectorAll('.piece-for-quote-checkbox').forEach(cb => {
                cb.addEventListener('change', (e) => {
                    const depGroup = e.target.dataset.dependencyGroup;
                    if (depGroup) {
                        document.querySelectorAll(`.piece-for-quote-checkbox[data-dependency-group="${depGroup}"]`).forEach(otherCb => {
                            otherCb.checked = e.target.checked;
                        });
                    }
                });
            });

            document.getElementById('createQuoteBtn').addEventListener('click', () => {
                const selectedPieces = Array.from(document.querySelectorAll('.piece-for-quote-checkbox:checked')).map(cb => cb.dataset.pieceName);
                handleCreateQuoteRequest(projectId, selectedPieces);
            });

            document.getElementById('editProjectBtn').addEventListener('click', () => openAddEditModal('masterProjects', projectId));
            document.getElementById('deleteProjectBtn').addEventListener('click', () => handleDeleteProject(projectId));
        }

        function buildPieceTimeline(piece, project) {
            let events = [];

            events.push({
                title: 'ایجاد در پروژه',
                date: project.createdAt,
                icon: 'fa-file-signature',
                color: 'bg-gray-500',
                details: ''
            });

            const quoteRequest = appState.quotationRequests.find(q => q.projectId === project.id && q.pieceNames.includes(piece.name));
            const order = appState.orders.find(o => o.masterProjectId === project.id && o.pieces.includes(piece.name));

            if (quoteRequest) {
                const suppliers = (quoteRequest.suppliers || []).map(s => s.name).join('، ');
                events.push({
                    title: 'ارسال برای قیمت‌گیری',
                    date: quoteRequest.createdAt,
                    icon: 'fa-comments-dollar',
                    color: 'bg-cyan-500',
                    details: `<p class="text-xs text-slate-500">ارسال شده به: ${suppliers}</p>`
                });
                if (quoteRequest.isArchived) {
                    events.push({
                        title: 'درخواست قیمت بایگانی شد',
                        date: quoteRequest.updatedAt,
                        icon: 'fa-archive',
                        color: 'bg-red-500',
                        details: `<p class="text-xs text-slate-500">علت: ${quoteRequest.archiveReason || 'نامشخص'}</p>`
                    });
                }
            }

            if (order) {
                const producer = appState.producers.find(p => p.id === order.producerId);
                const orderCreationEvent = (order.statusHistory || []).find(h => h.status === 'new');
                const orderDate = orderCreationEvent ? orderCreationEvent.date : order.createdAt;
                events.push({
                    title: 'ایجاد سفارش',
                    date: orderDate,
                    icon: 'fa-plus-circle',
                    color: 'bg-blue-500',
                    details: `<p class="text-xs text-slate-500">سازنده: ${producer?.name || ''} / کد: ${order.orderCode}</p>`
                });

                (order.statusHistory || []).forEach(hist => {
                    const statusInfo = config.statuses[hist.status] || config.shippingStatuses[hist.status];
                    if (statusInfo && statusInfo.title !== 'سفارش جدید') {
                        events.push({
                            title: statusInfo.title,
                            date: hist.date,
                            icon: statusInfo.icon,
                            color: `bg-${statusInfo.color.replace('-500', '-600')}`,
                            details: ''
                        });
                    }
                });

                const preShipment = appState.preForwardingShipments.find(s => s.id === order.preForwardingShipmentId);
                if (preShipment) {
                    const forwarder = appState.forwarders.find(f => f.id === preShipment.forwarderId);
                    events.push({
                        title: 'ارسال به فورواردر',
                        date: preShipment.createdAt,
                        icon: 'fa-truck',
                        color: 'bg-purple-600',
                        details: `<p class="text-xs text-slate-500">${forwarder?.name}</p>`
                    });

                    const mainShipment = appState.mainShipments.find(s => s.id === preShipment.mainShipmentId);
                    if (mainShipment) {
                        events.push({
                            title: 'حمل اصلی',
                            date: mainShipment.createdAt,
                            icon: 'fa-shipping-fast',
                            color: 'bg-teal-600',
                            details: `<p class="text-xs text-slate-500">کد مرجع: ${mainShipment.forwarderRef}</p>`
                        });
                    }
                }
                if (order.isDelivered) {
                    const deliveredEvent = (order.statusHistory || []).find(h => h.status === 'delivered');
                    events.push({
                        title: 'تحویل مشتری',
                        date: deliveredEvent.date,
                        icon: 'fa-box-check',
                        color: 'bg-emerald-600',
                        details: ''
                    });
                }

                if (order.isArchived) {
                    events.push({
                        title: 'سفارش بایگانی شد',
                        date: order.updatedAt,
                        icon: 'fa-archive',
                        color: 'bg-red-500',
                        details: `<p class="text-xs text-slate-500">علت: ${order.archiveReason}</p>`
                    });
                }
            }
            if (piece.isArchived) {
                events.push({
                    title: 'قطعه بایگانی شد',
                    date: project.updatedAt,
                    icon: 'fa-archive',
                    color: 'bg-red-500',
                    details: `<p class="text-xs text-slate-500">علت: ${piece.archiveReason}</p>`
                });
            }

            events.sort((a, b) => (a.date?.seconds || 0) - (b.date?.seconds || 0));

            return events.map(event => `
            <div class="relative flex items-start timeline-item">
                <div class="absolute top-0 right-5 w-px h-full bg-slate-200 timeline-line"></div>
                <div class="flex-shrink-0 w-10 h-10 rounded-full ${event.color} text-white flex items-center justify-center z-10">
                    <i class="fas ${event.icon}"></i>
                </div>
                <div class="mr-4">
                    <p class="font-semibold text-sm">${event.title}</p>
                    <p class="text-xs text-slate-500">${formatDateTime(event.date)}</p>
                    ${event.details}
                </div>
            </div>
        `).join('');
        }

        // --- PART 3: CONTINUATION OF SCRIPT (QUOTING, SHIPPING, ACCOUNTING) ---

        async function handleCreateQuoteRequest(projectId, pieceNames) {
            if (pieceNames.length === 0) {
                showToast('هیچ قطعه جدیدی برای قیمت‌گیری انتخاب نشده است.', 'info');
                return;
            }

            const producersHtml = appState.producers.map(p => `<div class="p-2"><input type="checkbox" class="ml-2 producer-checkbox" data-id="${p.id}" data-name="${p.name}"><span>${p.name}</span></div>`).join('');
            openModal(`<div class="p-6"><button onclick="closeModal()" class="modal-close-btn">&times;</button><h3 class="text-xl font-bold mb-4">انتخاب تامین‌کنندگان</h3><div class="modal-body">${producersHtml}</div>
            <div class="pt-4 mt-4 border-t flex justify-end">
            <button id="submitQuote" class="px-4 py-2 bg-blue-600 text-white rounded">ثبت درخواست</button></div></div>`);

            document.getElementById('submitQuote').addEventListener('click', async () => {
                const selectedProducers = Array.from(document.querySelectorAll('.producer-checkbox:checked'))
                    .map(cb => ({
                        producerId: cb.dataset.id,
                        name: cb.dataset.name,
                        prices: []
                    }));
                if (selectedProducers.length === 0) {
                    showToast('حداقل یک تامین‌کننده انتخاب کنید.', 'error');
                    return;
                }

                showLoader();
                try {
                    const project = appState.masterProjects.find(p => p.id === projectId);
                    await addDoc(collection(db, 'quotationRequests'), {
                        projectId,
                        pieceNames,
                        suppliers: selectedProducers,
                        status: 'pending',
                        customerId: project.customerId,
                        ...getAuditData()
                    });
                    showToast("درخواست قیمت با موفقیت ثبت شد.");
                    closeModal();
                    switchView('quotingView');
                } catch (err) {
                    showToast("خطا در ثبت درخواست.", "error");
                } finally {
                    hideLoader();
                }
            });
        }

        window.openQuotationDetailsModal = function(reqId) {
            const req = appState.quotationRequests.find(r => r.id === reqId);
            if (!req) {
                showToast('درخواست قیمت یافت نشد.', 'error');
                return;
            }
            const project = appState.masterProjects.find(p => p.id === req.projectId);

            let tableHeader = `<th class="p-2 border">قطعه</th>` + (req.suppliers || []).map(s => `<th class="p-2 border">${s.name}</th>`).join('');
            let tableBody = (req.pieceNames || []).map(pieceName => {
                let row = `<td class="p-2 border font-semibold">${pieceName}</td>`;
                (req.suppliers || []).forEach(s => {
                    const priceInfo = (s.prices || []).find(p => p.pieceName === pieceName);
                    row += `<td class="p-2 border space-y-2">
                    <input type="number" class="price-input w-full p-1 border rounded" data-producer-id="${s.producerId}" data-piece-name="${pieceName}" value="${priceInfo?.price || ''}" placeholder="قیمت">
                    <input type="number" class="time-input w-full p-1 border rounded" data-producer-id="${s.producerId}" data-piece-name="${pieceName}" value="${priceInfo?.deliveryTime || ''}" placeholder="زمان (روز)">
                </td>`;
                });
                return `<tr>${row}</tr>`;
            }).join('');

            const modalHtml = `
        <div class="p-6 flex flex-col h-full">
             <button onclick="closeModal()" class="modal-close-btn">&times;</button>
            <h3 class="text-2xl font-bold flex-shrink-0">ثبت قیمت‌ها برای پروژه: ${project?.code || ''}</h3>
            <div class="modal-body flex-grow overflow-x-auto my-4"><table class="w-full text-sm text-center"><thead><tr class="bg-gray-100">${tableHeader}</tr></thead><tbody>${tableBody}</tbody></table></div>
            <div class="flex justify-between items-center pt-4 mt-auto border-t flex-shrink-0">
                <div>
                    <button id="rejectQuoteBtn" class="px-4 py-2 bg-red-600 text-white rounded-lg">رد کردن و بایگانی</button>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="px-4 py-2 bg-gray-200 rounded-lg" onclick="closeModal()">بستن</button>
                    <button type="button" id="addSuppliersBtn" class="px-4 py-2 bg-yellow-500 text-white rounded-lg">قیمت‌گیری مجدد</button>
                    <button type="button" id="updatePricesBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg">ذخیره موقت قیمت‌ها</button>
                    <button id="finalizeQuoteBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg">نهایی‌سازی و ایجاد سفارش</button>
                </div>
            </div>
        </div>`;
            openModal(modalHtml, 'max-w-5xl');

            document.getElementById('updatePricesBtn').addEventListener('click', () => handleUpdateQuotePrices(reqId, false));
            document.getElementById('finalizeQuoteBtn').addEventListener('click', () => handleFinalizeQuote(reqId));
            document.getElementById('rejectQuoteBtn').addEventListener('click', () => handleRejectQuote(reqId));
            document.getElementById('addSuppliersBtn').addEventListener('click', () => handleReQuote(reqId));
        }

        async function handleUpdateQuotePrices(reqId, silent = false) {
            const req = appState.quotationRequests.find(r => r.id === reqId);
            const updatedSuppliers = JSON.parse(JSON.stringify(req.suppliers || []));

            document.querySelectorAll('.price-input, .time-input').forEach(input => {
                const {
                    producerId,
                    pieceName
                } = input.dataset;
                const value = parseFloat(input.value) || 0;
                const isPrice = input.classList.contains('price-input');

                const supplier = updatedSuppliers.find(s => s.producerId === producerId);
                if (supplier) {
                    let priceInfo = (supplier.prices || []).find(p => p.pieceName === pieceName);
                    if (!priceInfo) {
                        if (!supplier.prices) supplier.prices = [];
                        priceInfo = {
                            pieceName: pieceName
                        };
                        supplier.prices.push(priceInfo);
                    }
                    if (isPrice) priceInfo.price = value;
                    else priceInfo.deliveryTime = value;
                }
            });

            if (!silent) showLoader();
            try {
                await updateDoc(doc(db, 'quotationRequests', reqId), {
                    suppliers: updatedSuppliers,
                    ...getAuditData(false)
                });
                if (!silent) showToast('قیمت‌ها ذخیره شدند.');
            } catch (e) {
                if (!silent) showToast('خطا در ذخیره.', 'error');
            } finally {
                if (!silent) hideLoader();
            }
        }

        async function handleReQuote(reqId) {
            await handleUpdateQuotePrices(reqId, true);
            const req = appState.quotationRequests.find(r => r.id === reqId);
            const existingSupplierIds = (req.suppliers || []).map(s => s.producerId);
            const availableProducers = appState.producers.filter(p => !existingSupplierIds.includes(p.id));

            if (availableProducers.length === 0) {
                showToast('تامین‌کننده دیگری برای افزودن وجود ندارد.', 'info');
                return;
            }

            const producersHtml = availableProducers.map(p => `<div class="p-2"><input type="checkbox" class="ml-2 producer-checkbox" data-id="${p.id}" data-name="${p.name}"><span>${p.name}</span></div>`).join('');
            openModal(`<div class="p-6"><button onclick="closeModal()" class="modal-close-btn">&times;</button><h3 class="text-xl font-bold mb-4">افزودن تامین‌کننده جدید</h3><div class="modal-body">${producersHtml}</div>
            <div class="pt-4 mt-4 border-t flex justify-end">
            <button id="addSelectedSuppliers" class="px-4 py-2 bg-blue-600 text-white rounded">افزودن</button></div></div>`);

            document.getElementById('addSelectedSuppliers').addEventListener('click', async () => {
                const newSuppliers = Array.from(document.querySelectorAll('.producer-checkbox:checked'))
                    .map(cb => ({
                        producerId: cb.dataset.id,
                        name: cb.dataset.name,
                        prices: []
                    }));

                if (newSuppliers.length === 0) {
                    showToast('تامین‌کننده‌ای انتخاب نشده است.', 'error');
                    return;
                }

                const updatedSuppliers = [...(req.suppliers || []), ...newSuppliers];
                showLoader();
                try {
                    await updateDoc(doc(db, 'quotationRequests', reqId), {
                        suppliers: updatedSuppliers
                    });
                    showToast('تامین‌کنندگان جدید اضافه شدند.');
                    closeModal();
                    openQuotationDetailsModal(reqId);
                } catch (e) {
                    showToast('خطا در افزودن تامین‌کننده.', 'error');
                } finally {
                    hideLoader();
                }
            });
        }

        async function handleFinalizeQuote(reqId) {
            await handleUpdateQuotePrices(reqId, true);
            const req = appState.quotationRequests.find(r => r.id === reqId);
            const project = appState.masterProjects.find(p => p.id === req.projectId);

            const piecesToSelect = (req.pieceNames || []).map(name => project.pieces.find(p => p.name === name)).filter(Boolean);
            const groupedPieces = {};
            const individualPieces = [];

            piecesToSelect.forEach(piece => {
                if (piece.dependencyGroup) {
                    if (!groupedPieces[piece.dependencyGroup]) groupedPieces[piece.dependencyGroup] = [];
                    groupedPieces[piece.dependencyGroup].push(piece.name);
                } else {
                    individualPieces.push(piece.name);
                }
            });

            let selectionHtml = individualPieces.map(pieceName => {
                let options = (req.suppliers || []).map(s => {
                    const priceInfo = (s.prices || []).find(p => p.pieceName === pieceName);
                    if (!priceInfo || !priceInfo.price) return '';
                    return `<option value="${s.producerId}">${s.name} - ${priceInfo.price.toLocaleString()} (${priceInfo.deliveryTime || 'N/A'} روز)</option>`;
                }).join('');
                return `<div class="grid grid-cols-12 gap-4 items-center"><label class="col-span-4">${pieceName}</label><select class="piece-supplier-selector col-span-6 w-full p-2 border rounded" data-piece-name="${pieceName}"><option value="">انتخاب سازنده</option>${options}</select><button class="archive-piece-btn col-span-2 text-red-500 text-xs hover:underline" data-piece-name="${pieceName}">بایگانی</button></div>`;
            }).join('');

            for (const groupId in groupedPieces) {
                const pieceNames = groupedPieces[groupId];
                let options = (req.suppliers || []).map(s => {
                    const allPricesAvailable = pieceNames.every(name => (s.prices || []).some(p => p.pieceName === name && p.price));
                    if (!allPricesAvailable) return '';
                    const totalGroupPrice = pieceNames.reduce((sum, name) => sum + (s.prices.find(p => p.pieceName === name)?.price || 0), 0);
                    const maxTime = Math.max(...pieceNames.map(name => s.prices.find(p => p.pieceName === name)?.deliveryTime || 0));
                    return `<option value="${s.producerId}">${s.name} - (جمع: ${totalGroupPrice.toLocaleString()}) (${maxTime} روز)</option>`;
                }).join('');
                selectionHtml += `<div class="grid grid-cols-12 gap-4 items-center bg-yellow-100 p-2 rounded-md"><label class="col-span-4 font-bold">گروه: ${pieceNames.join(', ')}</label><select class="group-supplier-selector col-span-6 w-full p-2 border rounded" data-group-id="${groupId}"><option value="">انتخاب سازنده</option>${options}</select><button class="archive-piece-btn col-span-2 text-red-500 text-xs hover:underline" data-piece-name="${pieceNames.join(',')}">بایگانی</button></div>`;
            }

            openModal(`<div class="p-6"><button onclick="closeModal()" class="modal-close-btn">&times;</button><h3 class="text-xl font-bold mb-4">انتخاب سازنده نهایی</h3>
        <div class="modal-body space-y-3">${selectionHtml}</div><div class="pt-4 mt-4 border-t flex justify-end">
        <button id="confirmOrderCreation" class="px-4 py-2 bg-green-600 text-white rounded">ایجاد سفارشات</button></div></div>`, 'max-w-lg');

            document.querySelectorAll('.archive-piece-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const pieceNamesToArchive = e.target.dataset.pieceName.split(',');
                    showPromptModal(`دلیل بایگانی قطعه/قطعات (${pieceNamesToArchive.join(', ')}) را وارد کنید:`, async (reason) => {
                        showLoader();
                        try {
                            const projectRef = doc(db, 'masterProjects', req.projectId);
                            const updatedPieces = project.pieces.map(p => {
                                if (pieceNamesToArchive.includes(p.name)) {
                                    return {
                                        ...p,
                                        isArchived: true,
                                        archiveReason: `رد شده توسط مشتری در قیمت‌گیری: ${reason}`
                                    };
                                }
                                return p;
                            });
                            await updateDoc(projectRef, {
                                pieces: updatedPieces
                            });
                            showToast('قطعات بایگانی شدند.');
                            e.target.closest('.grid').remove(); // Remove the row from UI
                        } catch (err) {
                            showToast('خطا در بایگانی.', 'error');
                        } finally {
                            hideLoader();
                        }
                    });
                });
            });

            document.getElementById('confirmOrderCreation').addEventListener('click', async () => {
                const ordersToCreate = {};
                document.querySelectorAll('.piece-supplier-selector').forEach(select => {
                    const producerId = select.value;
                    if (producerId) {
                        const pieceName = select.dataset.pieceName;
                        if (!ordersToCreate[producerId]) ordersToCreate[producerId] = [];
                        ordersToCreate[producerId].push(pieceName);
                    }
                });

                document.querySelectorAll('.group-supplier-selector').forEach(select => {
                    const producerId = select.value;
                    if (producerId) {
                        const groupId = select.dataset.groupId;
                        const pieceNames = groupedPieces[groupId];
                        if (!ordersToCreate[producerId]) ordersToCreate[producerId] = [];
                        ordersToCreate[producerId].push(...pieceNames);
                    }
                });

                if (Object.keys(ordersToCreate).length === 0) {
                    showToast('هیچ سازنده‌ای برای ایجاد سفارش انتخاب نشده است.', 'info');
                    return;
                }

                showLoader('در حال ایجاد سفارشات...');
                try {
                    const batch = writeBatch(db);
                    const lastOrderNumber = appState.orders.map(o => parseInt(o.orderCode?.split('-').pop()) || 0).reduce((max, c) => Math.max(max, c), 1000);
                    let codeCounter = 1;

                    for (const producerId in ordersToCreate) {
                        const newOrderRef = doc(collection(db, 'orders'));
                        batch.set(newOrderRef, {
                            pieces: ordersToCreate[producerId],
                            producerId,
                            masterProjectId: req.projectId,
                            customerId: req.customerId,
                            orderCode: `${project.code}-${lastOrderNumber + codeCounter++}`,
                            status: 'new',
                            isArchived: false,
                            stageDetails: {},
                            statusHistory: [{
                                status: 'new',
                                date: new Date()
                            }],
                            ...getAuditData(true)
                        });
                    }
                    batch.update(doc(db, 'quotationRequests', reqId), {
                        status: 'completed'
                    });
                    await batch.commit();
                    showToast('سفارشات با موفقیت ایجاد شدند.');
                    closeModal();
                } catch (e) {
                    showToast('خطا در ایجاد سفارشات.', 'error');
                    console.error(e)
                } finally {
                    hideLoader();
                }
            });
        }

        async function handleRejectQuote(reqId) {
            const req = appState.quotationRequests.find(r => r.id === reqId);
            showPromptModal('لطفا دلیل رد کردن و بایگانی این درخواست را وارد کنید:', async (reason) => {
                showLoader();
                try {
                    const batch = writeBatch(db);

                    batch.update(doc(db, 'quotationRequests', reqId), {
                        status: 'rejected',
                        isArchived: true,
                        archiveReason: reason,
                        ...getAuditData(false)
                    });

                    const projectRef = doc(db, 'masterProjects', req.projectId);
                    const project = appState.masterProjects.find(p => p.id === req.projectId);
                    if (project) {
                        const updatedPieces = project.pieces.map(p => {
                            if (req.pieceNames.includes(p.name)) {
                                return {
                                    ...p,
                                    isArchived: true,
                                    archiveReason: `رد شده در قیمت‌گیری: ${reason}`
                                };
                            }
                            return p;
                        });
                        batch.update(projectRef, {
                            pieces: updatedPieces
                        });
                    }

                    await batch.commit();
                    showToast('درخواست قیمت و قطعات مربوطه بایگانی شدند.');
                    closeModal();
                } catch (e) {
                    showToast('خطا در بایگانی.', 'error');
                } finally {
                    hideLoader();
                }
            });
        }

        // ... All other functions (shipping, accounting, delete, modals, utils) remain the same ...
        window.handleCreatePreShipment = async function() {
            const selectedIds = Array.from(document.querySelectorAll('#shipping-content .order-checkbox:checked')).map(chk => chk.dataset.id);
            if (selectedIds.length === 0) {
                showToast('لطفاً حداقل یک سفارش را انتخاب کنید.', 'error');
                return;
            }

            const forwarderOpts = appState.forwarders.map(f => `<option value="${f.id}">${f.name}</option>`).join('');
            openModal(`<form id="preShipmentForm" class="p-6 space-y-4"><button onclick="closeModal()" class="modal-close-btn">&times;</button><h2 class="text-xl font-bold">ایجاد بسته حمل داخلی</h2>
            <div><label class="block mb-1">فورواردر</label><select id="forwarderId" class="w-full p-2 border rounded bg-white" required>${forwarderOpts}</select></div>
            <div><label class="block mb-1">شماره رهگیری کلی</label><input id="trackingNumber" type="text" class="w-full p-2 border rounded" required></div>
            <div class="flex justify-end space-x-reverse space-x-2 pt-4"><button type="button" class="px-4 py-2 bg-gray-200 rounded" onclick="closeModal()">انصراف</button><button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded">ایجاد</button></div></form>`);

            document.getElementById('preShipmentForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                showLoader();
                try {
                    const batch = writeBatch(db);
                    const shipmentRef = doc(collection(db, 'preForwardingShipments'));
                    batch.set(shipmentRef, {
                        trackingNumber: e.target.querySelector('#trackingNumber').value,
                        forwarderId: e.target.querySelector('#forwarderId').value,
                        orderIds: selectedIds,
                        receivedOrderIds: [],
                        status: 'in_transit',
                        ...getAuditData(true)
                    });
                    selectedIds.forEach(id => batch.update(doc(db, 'orders', id), {
                        preForwardingShipmentId: shipmentRef.id
                    }));
                    await batch.commit();
                    showToast('بسته حمل داخلی ایجاد شد.');
                    closeModal();
                } catch (err) {
                    showToast('خطا در ایجاد بسته.', 'error');
                } finally {
                    hideLoader();
                }
            });
        }

        window.handleCreateMainShipment = async function() {
            const selectedIds = Array.from(document.querySelectorAll('#shipping-content .shipment-checkbox:checked')).map(chk => chk.dataset.id);
            if (selectedIds.length === 0) {
                showToast('لطفاً حداقل یک بسته را انتخاب کنید.', 'error');
                return;
            }

            openModal(`<form id="mainShipmentForm" class="p-6 space-y-4"><button onclick="closeModal()" class="modal-close-btn">&times;</button><h2 class="text-xl font-bold">ایجاد محموله اصلی</h2>
             <div><label class="block mb-1">کد مرجع فورواردر</label><input id="forwarderRef" type="text" class="w-full p-2 border rounded" required></div>
             <div><label class="block mb-1">نوع حمل</label><select id="shippingMethod" class="w-full p-2 border rounded bg-white"><option value="Air">هوایی</option><option value="Sea">دریایی</option><option value="Land">زمینی</option></select></div>
             <div class="flex justify-end pt-4"><button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded">ایجاد محموله</button></div></form>`);

            document.getElementById('mainShipmentForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                showLoader();
                try {
                    const batch = writeBatch(db);
                    const mainShipmentRef = doc(collection(db, 'mainShipments'));
                    const firstPreShipment = appState.preForwardingShipments.find(s => s.id === selectedIds[0]);

                    batch.set(mainShipmentRef, {
                        forwarderRef: e.target.querySelector('#forwarderRef').value,
                        shippingMethod: e.target.querySelector('#shippingMethod').value,
                        forwarderId: firstPreShipment?.forwarderId,
                        preShipmentIds: selectedIds,
                        status: 'shipped',
                        ...getAuditData(true)
                    });
                    selectedIds.forEach(id => batch.update(doc(db, 'preForwardingShipments', id), {
                        mainShipmentId: mainShipmentRef.id
                    }));
                    const allOrderIds = selectedIds.flatMap(id => appState.preForwardingShipments.find(s => s.id === id)?.orderIds || []);
                    allOrderIds.forEach(orderId => batch.update(doc(db, 'orders', orderId), {
                        mainShipmentId: mainShipmentRef.id
                    }));

                    await batch.commit();
                    showToast('محموله اصلی ایجاد شد.');
                    closeModal();
                } catch (err) {
                    showToast('خطا در ایجاد محموله.', 'error');
                    console.error(err)
                } finally {
                    hideLoader();
                }
            });
        }

        function createShipmentOrderDetailHtml(order) {
            if (!order) return '';
            const project = appState.masterProjects.find(p => p.id === order.masterProjectId);
            const customer = appState.customers.find(c => c.id === project?.customerId);
            const producer = appState.producers.find(p => p.id === order.producerId);
            return `
            <div class="p-3 bg-slate-100 rounded-lg border">
                <div class="flex justify-between items-center">
                    <p class="font-bold">${order.orderCode} - ${order.pieces.join(', ')}</p>
                    <p class="text-sm font-mono">${project?.code || 'N/A'}</p>
                </div>
                <div class="text-xs text-gray-600 mt-2 grid grid-cols-2 gap-1">
                    <p><strong>مشتری:</strong> ${customer?.name || 'N/A'}</p>
                    <p><strong>سازنده:</strong> ${producer?.name || 'N/A'}</p>
                    <p><strong>تاریخ تکمیل:</strong> ${formatDateTime(order.completedAt)}</p>
                </div>
            </div>
        `;
        }

        window.showPreShipmentDetails = function(shipmentId) {
            const shipment = appState.preForwardingShipments.find(s => s.id === shipmentId);
            if (!shipment) return;

            const ordersHtml = shipment.orderIds.map(orderId => {
                const order = appState.orders.find(o => o.id === orderId);
                if (!order) return '';
                const isReceived = (shipment.receivedOrderIds || []).includes(orderId);
                return `<div class="p-2 border-b grid grid-cols-12 items-center gap-4">
                <div class="col-span-1">
                    <input type="checkbox" class="ml-3 h-5 w-5 order-received-checkbox" data-order-id="${orderId}" ${isReceived ? 'checked' : ''}>
                </div>
                <div class="col-span-8">${createShipmentOrderDetailHtml(order)}</div>
                <div class="col-span-3">
                    <input type="text" class="p-1 border rounded text-sm w-full" data-order-id="${orderId}" value="${order.internalTrackingNumber || ''}" placeholder="رهگیری داخلی">
                </div>
            </div>`;
            }).join('');

            openModal(`<div class="p-6 flex flex-col h-full"><button onclick="closeModal()" class="modal-close-btn">&times;</button>
            <h3 class="text-xl font-bold flex-shrink-0">جزئیات بسته داخلی: ${shipment.trackingNumber}</h3>
            <div class="modal-body my-4">${ordersHtml}</div>
            <div class="flex justify-between items-center pt-4 mt-auto border-t flex-shrink-0">
                <button type="button" class="px-4 py-2 bg-gray-200 rounded-lg" onclick="closeModal()">بستن</button>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg" onclick="handleUpdateReceivedOrders('${shipmentId}')">ذخیره وضعیت دریافت</button>
            </div>
        </div>`, 'max-w-4xl');
        }

        window.handleUpdateReceivedOrders = async function(shipmentId) {
            const receivedOrderIds = Array.from(document.querySelectorAll('.order-received-checkbox:checked')).map(cb => cb.dataset.orderId);
            const shipment = appState.preForwardingShipments.find(s => s.id === shipmentId);
            const allReceived = shipment.orderIds.length === receivedOrderIds.length;

            showLoader();
            try {
                const batch = writeBatch(db);
                batch.update(doc(db, 'preForwardingShipments', shipmentId), {
                    receivedOrderIds,
                    status: allReceived ? 'received' : 'in_transit'
                });

                document.querySelectorAll('.order-tracking-input').forEach(input => {
                    const orderId = input.dataset.orderId;
                    const trackingNumber = input.value;
                    batch.update(doc(db, 'orders', orderId), {
                        internalTrackingNumber: trackingNumber
                    });
                });

                await batch.commit();
                showToast('وضعیت دریافت به‌روزرسانی شد.');
                closeModal();
            } catch (e) {
                showToast('خطا در به‌روزرسانی.', 'error');
            } finally {
                hideLoader();
            }
        }

        window.showMainShipmentDetails = function(shipmentId) {
            const shipment = appState.mainShipments.find(s => s.id === shipmentId);
            if (!shipment) return;

            let detailsHtml = (shipment.preShipmentIds || []).map(preId => {
                const preShipment = appState.preForwardingShipments.find(p => p.id === preId);
                if (!preShipment) return '';
                const ordersHtml = (preShipment.orderIds || []).map(orderId => {
                    const order = appState.orders.find(o => o.id === orderId);
                    return createShipmentOrderDetailHtml(order);
                }).join('<div class="my-2"></div>');

                return `<details class="bg-white rounded-lg border group mb-3">
                <summary class="flex justify-between items-center p-3 cursor-pointer list-none">
                    <span class="font-semibold">بسته داخلی: ${preShipment.trackingNumber}</span>
                    <i class="fas fa-chevron-down group-open:rotate-180 transition-transform"></i>
                </summary>
                <div class="p-4 border-t bg-slate-50/50 space-y-2">${ordersHtml}</div>
            </details>`;
            }).join('');

            openModal(`<div class="p-6 flex flex-col h-full"><button onclick="closeModal()" class="modal-close-btn">&times;</button>
            <h3 class="text-xl font-bold flex-shrink-0">جزئیات محموله: ${shipment.forwarderRef}</h3>
            <div class="modal-body my-4">${detailsHtml}</div>
            <div class="flex justify-between items-center pt-4 mt-auto border-t flex-shrink-0">
                <button class="px-4 py-2 bg-gray-500 text-white rounded-lg" onclick="handleArchiveMainShipment('${shipment.id}')">بایگانی محموله</button>
                <div class="flex items-center gap-2">
                    <button type="button" class="px-4 py-2 bg-gray-200 rounded-lg" onclick="closeModal()">بستن</button>
                    ${shipment.status !== 'delivered' ? `<button class="px-4 py-2 bg-green-600 text-white rounded-lg" onclick="handleMarkAsDelivered('${shipment.id}')">علامت‌گذاری به عنوان تحویل شده</button>` : ''}
                </div>
            </div>
        </div>`, 'max-w-4xl');
        }

        window.handleMarkAsDelivered = async function(shipmentId) {
            const shipment = appState.mainShipments.find(s => s.id === shipmentId);
            if (!shipment) return;

            showConfirmModal('آیا از تحویل این محموله اطمینان دارید؟', async () => {
                showLoader();
                try {
                    const batch = writeBatch(db);
                    const deliveredAt = new Date();
                    batch.update(doc(db, 'mainShipments', shipmentId), {
                        status: 'delivered',
                        deliveredAt: serverTimestamp()
                    });

                    const orderIdsToUpdate = shipment.preShipmentIds.flatMap(preId => {
                        const preShipment = appState.preForwardingShipments.find(p => p.id === preId);
                        return preShipment ? preShipment.orderIds : [];
                    });

                    orderIdsToUpdate.forEach(orderId => {
                        batch.update(doc(db, 'orders', orderId), {
                            isDelivered: true,
                            statusHistory: arrayUnion({
                                status: 'delivered',
                                date: deliveredAt
                            })
                        });
                    });

                    await batch.commit();
                    showToast('محموله به عنوان تحویل شده علامت‌گذاری شد.');
                    closeModal();
                } catch (e) {
                    showToast('خطا در عملیات.', 'error');
                } finally {
                    hideLoader();
                }
            });
        }

        window.handleArchiveMainShipment = async function(shipmentId) {
            showConfirmModal('آیا از بایگانی این محموله اطمینان دارید؟', async () => {
                showLoader();
                try {
                    await updateDoc(doc(db, 'mainShipments', shipmentId), {
                        isArchived: true,
                        archiveReason: 'بایگانی توسط کاربر'
                    });
                    showToast('محموله بایگانی شد.');
                    closeModal();
                } catch (e) {
                    showToast('خطا در بایگانی.', 'error');
                } finally {
                    hideLoader();
                }
            });
        }

        window.openTransactionModal = function(orderId = null) {
            const orderOpts = appState.orders.filter(o => !o.isArchived).map(o => `<option value="${o.id}" ${orderId === o.id ? 'selected':''}>${o.orderCode} - ${o.pieces.join(', ')}</option>`).join('');
            const customerOpts = appState.customers.map(c => `<option value="${c.id}">${c.name}</option>`).join('');

            const modalHtml = `
        <form id="transactionForm" class="p-6 space-y-4"><button onclick="closeModal()" class="modal-close-btn">&times;</button><h2 class="text-xl font-bold">ثبت تراکنش جدید</h2>
            <div><label>نوع تراکنش</label><select id="type" class="w-full p-2 border rounded bg-white"><option value="customer_payment">دریافت از مشتری</option><option value="supplier_payment">پرداخت به سازنده</option></select></div>
            <div id="party-container"></div>
            <div><label>مبلغ</label><input id="amount" type="number" class="w-full p-2 border rounded"></div>
            <div><label>واحد پول</label><select id="currency" class="w-full p-2 border rounded bg-white"><option value="IRR">ریال</option><option value="USD">دلار</option><option value="CNY">یوان</option></select></div>
            <div><label>نرخ تبدیل (اگر نیاز است)</label><input id="exchangeRate" type="number" class="w-full p-2 border rounded" placeholder="مثلا برای تبدیل دلار به ریال"></div>
            <div><label>سفارش مرتبط</label><select id="orderId" class="w-full p-2 border rounded bg-white"><option value="">بدون سفارش</option>${orderOpts}</select></div>
            <div><label>تاریخ</label><input id="date" type="date" class="w-full p-2 border rounded"></div>
            <div class="pt-4 mt-4 border-t flex justify-end"><button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">ثبت تراکنش</button></div>
        </form>`;
            openModal(modalHtml, 'max-w-lg');

            const partyContainer = document.getElementById('party-container');
            const typeSelect = document.getElementById('type');

            function updatePartySelect() {
                if (typeSelect.value === 'customer_payment') {
                    partyContainer.innerHTML = `<div><label>مشتری</label><select id="partyId" class="w-full p-2 border rounded bg-white">${customerOpts}</select></div>`;
                } else {
                    partyContainer.innerHTML = `<p class="text-sm text-red-500">بخش پرداخت به سازنده در حال توسعه است</p>`;
                }
            }
            updatePartySelect();
            typeSelect.addEventListener('change', updatePartySelect);

            document.getElementById('transactionForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const form = e.target;
                const data = {
                    type: form.type.value,
                    party: {
                        type: form.type.value === 'customer_payment' ? 'customer' : 'producer',
                        id: form.partyId.value
                    },
                    amount: parseFloat(form.amount.value) || 0,
                    currency: form.currency.value,
                    exchangeRate: parseFloat(form.exchangeRate.value) || 1,
                    orderId: form.orderId.value || null,
                    date: form.date.value,
                    ...getAuditData(true)
                };
                showLoader();
                try {
                    await addDoc(collection(db, 'transactions'), data);
                    showToast('تراکنش با موفقیت ثبت شد.');
                    closeModal();
                } catch (err) {
                    showToast('خطا در ثبت.', 'error');
                } finally {
                    hideLoader();
                }
            });
        }

        async function handleDeleteProject(projectId) {
            showConfirmModal('آیا از بایگانی این پروژه و تمام موارد مرتبط با آن اطمینان دارید؟', async () => {
                showLoader('در حال بایگانی پروژه...');
                try {
                    const batch = writeBatch(db);
                    const archiveReason = 'پروژه اصلی بایگانی شد';

                    batch.update(doc(db, 'masterProjects', projectId), {
                        isArchived: true,
                        archiveReason: 'بایگانی توسط کاربر'
                    });

                    appState.orders.filter(o => o.masterProjectId === projectId).forEach(order => {
                        batch.update(doc(db, 'orders', order.id), {
                            isArchived: true,
                            archiveReason
                        });
                    });

                    appState.quotationRequests.filter(q => q.projectId === projectId).forEach(quote => {
                        batch.update(doc(db, 'quotationRequests', quote.id), {
                            isArchived: true,
                            archiveReason
                        });
                    });

                    await batch.commit();
                    showToast('پروژه با موفقیت بایگانی شد.');
                    closeModal();
                } catch (err) {
                    showToast('خطا در بایگانی پروژه.', 'error');
                    console.error("Error archiving project:", err);
                } finally {
                    hideLoader();
                }
            });
        }

        window.handleDelete = async function(type, id) {
            showConfirmModal('آیا از حذف این آیتم اطمینان دارید؟ این عمل غیرقابل بازگشت است.', async () => {
                showLoader();
                try {
                    if (type === 'customers') {
                        const hasProjects = appState.masterProjects.some(p => p.customerId === id && !p.isArchived);
                        if (hasProjects) {
                            showToast('این مشتری دارای پروژه فعال است و قابل حذف نیست.', 'error');
                            hideLoader();
                            return;
                        }
                    }
                    if (type === 'producers') {
                        const hasOrders = appState.orders.some(o => o.producerId === id && !o.isArchived);
                        if (hasOrders) {
                            showToast('این سازنده دارای سفارش فعال است و قابل حذف نیست.', 'error');
                            hideLoader();
                            return;
                        }
                    }
                    await deleteDoc(doc(db, type, id));
                    showToast('آیتم حذف شد.');
                } catch (err) {
                    showToast('خطا در حذف.', 'error');
                } finally {
                    hideLoader();
                }
            });
        }

        window.handlePermanentDelete = async function(type, id) {
            showConfirmModal('این آیتم برای همیشه حذف خواهد شد. آیا اطمینان دارید؟', async () => {
                showLoader('در حال حذف...');
                try {
                    await deleteDoc(doc(db, type, id));
                    showToast('آیتم برای همیشه حذف شد.');
                } catch (err) {
                    showToast('خطا در حذف.', 'error');
                } finally {
                    hideLoader();
                }
            });
        }

        window.handleUnarchive = async function(type, id) {
            showConfirmModal('آیا میخواهید این آیتم را از بایگانی خارج کنید؟', async () => {
                showLoader('در حال بازیابی...');
                try {
                    await updateDoc(doc(db, type, id), {
                        isArchived: false,
                        archiveReason: ''
                    });
                    showToast('آیتم از بایگانی خارج شد.');
                } catch (err) {
                    showToast('خطا در بازیابی.', 'error');
                } finally {
                    hideLoader();
                }
            });
        }

        window.showArchivedItemDetails = function(type, id) {
            const item = appState[type].find(i => i.id === id);
            if (!item) return;

            let timelineHtml = '';
            let title = 'جزئیات آیتم بایگانی شده';

            if (type === 'masterProjects') {
                title = `پروژه بایگانی شده: ${item.code}`;
                timelineHtml = (item.pieces || []).map(p => {
                    return `<details class="bg-white rounded-lg border group"><summary class="p-3 font-semibold list-none cursor-pointer">${p.name}</summary><div class="p-4 border-t">${buildPieceTimeline(p, item)}</div></details>`
                }).join('');
            } else if (type === 'orders') {
                title = `سفارش بایگانی شده: ${item.orderCode}`;
                const project = appState.masterProjects.find(p => p.id === item.masterProjectId);
                if (project) {
                    timelineHtml = (item.pieces || []).map(pName => {
                        const piece = project.pieces.find(p => p.name === pName);
                        return piece ? buildPieceTimeline(piece, project) : '';
                    }).join('');
                }
            } else {
                timelineHtml = '<p>نمایش تاریخچه برای این نوع آیتم در حال توسعه است.</p>';
            }

            const modalHtml = `
            <div class="p-6 flex flex-col h-full">
                <button onclick="closeModal()" class="modal-close-btn">&times;</button>
                <div class="flex-shrink-0 border-b pb-4 mb-4">
                    <h2 class="text-xl font-bold">${title}</h2>
                    <p class="mt-2 p-2 bg-red-100 text-red-700 rounded-md"><b>علت بایگانی:</b> ${item.archiveReason || 'نامشخص'}</p>
                </div>
                <div class="modal-body space-y-3">
                    ${timelineHtml}
                </div>
                <div class="flex justify-end pt-4 mt-auto border-t">
                    <button type="button" class="px-4 py-2 bg-gray-200 rounded" onclick="closeModal()">بستن</button>
                </div>
            </div>`;
            openModal(modalHtml, 'max-w-4xl');
        }

        // --- UTILITIES ---
        function showLoader(message = 'در حال پردازش...') {
            DOM.loader.querySelector('p').textContent = message;
            DOM.loader.classList.remove('hidden');
        }

        function hideLoader() {
            DOM.loader.classList.add('hidden');
        }

        function showToast(message, type = 'success') {
            DOM.toast.textContent = message;
            DOM.toast.className = `fixed top-5 right-5 text-white px-6 py-3 rounded-lg shadow-lg transform z-[101] toast ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            DOM.toast.style.transform = 'translateX(0)';
            setTimeout(() => {
                DOM.toast.style.transform = 'translateX(120%)';
            }, 3500);
        }

        window.openModal = function(html, sizeClass = 'max-w-4xl') {
            DOM.modalContent.className = `bg-white rounded-2xl shadow-2xl w-full ${sizeClass} modal-content transform scale-95`;
            DOM.modalContent.innerHTML = html;
            DOM.modal.classList.remove('hidden');
            setTimeout(() => {
                DOM.modal.classList.remove('opacity-0');
                DOM.modalContent.classList.remove('scale-95');
            }, 10);
        }

        window.closeModal = function() {
            DOM.modalContent.classList.add('scale-95');
            DOM.modal.classList.add('opacity-0');
            setTimeout(() => {
                DOM.modal.classList.add('hidden');
                DOM.modalContent.innerHTML = '';
            }, 300);
        }

        window.showConfirmModal = function(message, onConfirm) {
            const modalHtml = `
            <div class="p-6 text-center">
                 <button onclick="closeModal()" class="modal-close-btn">&times;</button>
                <p class="text-lg mb-6">${message}</p>
                <div class="flex justify-center space-x-reverse space-x-4">
                    <button id="confirm-no-btn" class="px-6 py-2 bg-gray-200 rounded-lg">خیر</button>
                    <button id="confirm-yes-btn" class="px-6 py-2 bg-red-600 text-white rounded-lg">بله</button>
                </div>
            </div>`;
            openModal(modalHtml, 'max-w-sm');
            document.getElementById('confirm-yes-btn').addEventListener('click', () => {
                onConfirm();
            });
            document.getElementById('confirm-no-btn').addEventListener('click', closeModal);
        }

        window.showPromptModal = function(message, onConfirm) {
            const modalHtml = `
            <div class="p-6">
                 <button onclick="closeModal()" class="modal-close-btn">&times;</button>
                <p class="text-lg mb-4">${message}</p>
                <textarea id="prompt-input" class="w-full p-2 border rounded h-24" placeholder="دلیل..."></textarea>
                <div class="flex justify-end space-x-reverse space-x-2 pt-4 mt-4 border-t">
                    <button id="prompt-cancel-btn" class="px-4 py-2 bg-gray-200 rounded-lg">انصراف</button>
                    <button id="prompt-confirm-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">تایید</button>
                </div>
            </div>`;
            openModal(modalHtml, 'max-w-md');
            document.getElementById('prompt-confirm-btn').addEventListener('click', () => {
                const reason = document.getElementById('prompt-input').value.trim();
                if (reason) {
                    onConfirm(reason);
                } else {
                    showToast('لطفا دلیل را وارد کنید.', 'error');
                }
            });
            document.getElementById('prompt-cancel-btn').addEventListener('click', closeModal);
        }

        // --- INITIALIZE ---
        initializeFirebase();
    </script>
</body>

</html>