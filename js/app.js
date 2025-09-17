document.addEventListener('DOMContentLoaded', function() {
    // --- GLOBAL STATE & API CONFIG ---
    const API_BASE = 'api/handler.php';
    let stocks = [], cashTransactions = [], caris = [], invoices = [], cheques = [], stockMovements = [], productDescriptions = [];
    let cashChart, currentEditItem = null, recognizedInvoiceData = null, productToAddFromBarcode = null;
    let geminiApiKey = localStorage.getItem('pre-accounting-app_geminiApiKey') || '';

    // --- DOM ELEMENT MAPPING ---
    const dom = {
        loginWrapper: document.getElementById('login-wrapper'),
        loginForm: document.getElementById('login-form'),
        mainApp: document.getElementById('main-app'),
        logoutBtn: document.getElementById('logout-btn'),
        tabButtons: document.querySelectorAll('.tab-btn'),
        stockTableBody: document.getElementById('stock-table-body'),
        addProductBtn: document.getElementById('add-product-btn'),
        productNameInput: document.getElementById('product-name'),
        productStockInput: document.getElementById('product-stock'),
        cashBalanceSpan: document.getElementById('cash-balance'),
        cashTableBody: document.getElementById('cash-table-body'),
        addCashBtn: document.getElementById('add-cash-btn'),
        cashDescriptionInput: document.getElementById('cash-description'),
        cashDateInput: document.getElementById('cash-date'),
        cashAmountInput: document.getElementById('cash-amount'),
        cashTypeInput: document.getElementById('cash-type'),
        cariTableBody: document.getElementById('cari-table-body'),
        addCariBtn: document.getElementById('add-cari-btn'),
        cariNameInput: document.getElementById('cari-name'),
        cariTypeInput: document.getElementById('cari-type'),
        cariFilterInput: document.getElementById('cari-filter-input'),
        invoiceTableBody: document.getElementById('invoice-table-body'),
        addInvoiceBtn: document.getElementById('add-invoice-btn'),
        invoiceNumberInput: document.getElementById('invoice-number'),
        invoiceDateInput: document.getElementById('invoice-date'),
        invoiceCariSelect: document.getElementById('invoice-cari'),
        invoiceTotalInput: document.getElementById('invoice-total'),
        invoiceTypeInput: document.getElementById('invoice-type'),
        excelExportBtn: document.getElementById('excel-export-btn'),
        editModal: document.getElementById('edit-modal'),
        editForm: document.getElementById('edit-form'),
        editModalCloseBtn: document.getElementById('edit-modal-close-btn'),
        confirmModal: document.getElementById('confirm-modal'),
        notification: document.getElementById('notification')
    };

    // --- UTILITY & HELPER FUNCTIONS ---
    pdfjsLib.GlobalWorkerOptions.workerSrc = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js`;

    function showMessage(message, duration = 3000) {
        dom.notification.textContent = message;
        dom.notification.classList.remove('hidden');
        setTimeout(() => dom.notification.classList.add('hidden'), duration);
    }

    function formatDate(skt) {
        if (!skt || skt.length !== 6) return skt;
        return `20${skt.substring(0, 2)}-${skt.substring(2, 4)}-${skt.substring(4, 6)}`;
    }

    // --- API ABSTRACTION LAYER ---
    async function apiRequest(entity, method = 'GET', data = null) {
        let url = `${API_BASE}?entity=${entity}`;
        if (method === 'DELETE' && data && data.id) url += `&id=${data.id}`;

        const options = {
            method,
            headers: { 'Content-Type': 'application/json' },
        };
        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            if (response.status === 401) { window.location.reload(); return { success: false, message: 'Unauthorized' }; }
            if (!response.ok) { throw new Error(`Server error: ${response.statusText}`); }
            const result = await response.json();
            if (!result.success) { showMessage('API Error: ' + (result.message || 'Unknown error')); }
            return result;
        } catch (error) {
            showMessage('Network Error: ' + error.message);
            return { success: false, message: error.message };
        }
    }

    // --- CORE APPLICATION LOGIC ---
    async function initialize() {
        if (!dom.mainApp.classList.contains('hidden')) {
            await loadAllData();
        }
        attachEventListeners();
    }

    async function loadAllData() {
        const results = await Promise.all([
            apiRequest('stocks'), apiRequest('cashTransactions'), apiRequest('caris'),
            apiRequest('invoices'), apiRequest('cheques'), apiRequest('stockMovements'),
            apiRequest('productDescriptions')
        ]);

        stocks = results[0].success ? results[0].data : [];
        cashTransactions = results[1].success ? results[1].data : [];
        caris = results[2].success ? results[2].data : [];
        invoices = results[3].success ? results[3].data : [];
        cheques = results[4].success ? results[4].data : [];
        stockMovements = results[5].success ? results[5].data : [];
        productDescriptions = results[6].success ? results[6].data : [];

        refreshUI();
    }

    function refreshUI() {
        recalculateCariBalances();
        renderStockTable();
        renderCashTable();
        renderCariTable();
        renderInvoiceTable();
        renderChequeTable();
        renderStockMovementsTable();
        populateCariSelects();
        populateStockOutProducts();
        populateStockReturnProducts();
        updateCashBalance();
    }

    // --- RENDER FUNCTIONS ---
    function renderStockTable() {
        dom.stockTableBody.innerHTML = '';
        const filterText = dom.cariFilterInput.value.toLowerCase();
        stocks.filter(s => s.name?.toLowerCase().includes(filterText)).forEach(p => {
            const row = dom.stockTableBody.insertRow();
            row.innerHTML = `
                <td class="px-6 py-4">${p.name}</td> <td class="px-6 py-4">${p.quantity}</td>
                <td class="px-6 py-4">${p.ubb || ''}</td> <td class="px-6 py-4">${p.lot || ''}</td>
                <td class="px-6 py-4">${p.skt || ''}</td>
                <td class="px-6 py-4"><button onclick="window.editItem('stocks', '${p.id}')">Edit</button> <button onclick="window.deleteItem('stocks', '${p.id}')">Delete</button></td>`;
        });
    }
    // ... other render functions ...
    function renderCashTable() { /* Similar to renderStockTable */ }
    function renderCariTable() { /* Similar to renderStockTable */ }
    function renderInvoiceTable() { /* Similar to renderStockTable */ }
    function renderChequeTable() { /* Similar to renderStockTable */ }
    function renderStockMovementsTable() { /* Similar to renderStockTable */ }

    // --- UI HELPERS ---
    function recalculateCariBalances() { /* Logic from original */ }
    function updateCashBalance() { /* Logic from original */ }
    function populateCariSelects() { /* Logic from original */ }
    function populateStockOutProducts() { /* Logic from original */ }
    function populateStockReturnProducts() { /* Logic from original */ }

    // --- EVENT HANDLERS & ATTACHMENT ---
    function attachEventListeners() {
        dom.loginForm.addEventListener('submit', handleLogin);
        dom.logoutBtn.addEventListener('click', handleLogout);
        dom.addProductBtn.addEventListener('click', handleAddStock);
        dom.addCashBtn.addEventListener('click', handleAddCash);
        dom.addCariBtn.addEventListener('click', handleAddCari);
        dom.addInvoiceBtn.addEventListener('click', handleAddInvoice);
        dom.addChequeBtn.addEventListener('click', handleAddCheque);
        dom.editForm.addEventListener('submit', handleEditSubmit);

        dom.tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                document.querySelector('.tab-btn.active').classList.remove('active');
                document.querySelector('.tab-content.active').classList.remove('active');
                button.classList.add('active');
                document.getElementById(button.dataset.tab).classList.add('active');
            });
        });
    }

    async function handleLogin(e) {
        e.preventDefault();
        const response = await fetch('api/login.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: dom.loginForm.username.value, password: dom.loginForm.password.value })
        });
        const result = await response.json();
        if (result.success) window.location.reload();
        else showMessage(result.message || 'Login failed');
    }

    async function handleLogout() {
        await fetch('api/logout.php');
        window.location.reload();
    }

    async function handleAddStock() {
        const name = dom.productNameInput.value;
        const quantity = parseInt(dom.productStockInput.value);
        if (name && !isNaN(quantity) && quantity >= 0) {
            const result = await apiRequest('stocks', 'POST', { name, quantity });
            if (result.success) {
                stocks.push(result.data);
                refreshUI();
                showMessage('Stock added.');
                dom.productNameInput.value = ''; dom.productStockInput.value = '';
            }
        } else { showMessage('Invalid stock data.'); }
    }
    // ... other handleAdd functions for cash, cari, etc. ...
    async function handleAddCash() { /* similar to handleAddStock */ }
    async function handleAddCari() { /* similar to handleAddStock */ }
    async function handleAddInvoice() { /* similar to handleAddStock */ }
    async function handleAddCheque() { /* similar to handleAddStock */ }

    async function handleEditSubmit(e) {
        e.preventDefault();
        if (!currentEditItem) return;
        const { type, id } = currentEditItem;
        const formElements = dom.editForm.elements;
        const updatedData = { id };
        for (const element of formElements) {
            if (element.name) updatedData[element.name] = element.value;
        }

        const result = await apiRequest(type, 'POST', updatedData); // POST for update
        if (result.success) {
            const itemIndex = window[type].findIndex(i => i.id === id);
            if (itemIndex !== -1) window[type][itemIndex] = { ...window[type][itemIndex], ...result.data };
            refreshUI();
            dom.editModal.style.display = 'none';
            showMessage('Item updated.');
        }
    }

    // Make functions available on window object for inline onclick calls
    window.editItem = function(type, id) {
        const item = window[type].find(i => i.id === id);
        if (!item) return;
        currentEditItem = { type, id };
        let formHtml = '';
        // Simplified form generation
        for (const key in item) {
            if (key !== 'id' && key !== 'balance') {
                formHtml += `<div><label>${key}</label><input name="${key}" value="${item[key]}" class="border p-1 w-full"></div>`;
            }
        }
        formHtml += '<button type="submit" class="bg-blue-500 text-white p-2">Save</button>';
        dom.editForm.innerHTML = formHtml;
        dom.editModal.style.display = 'flex';
    };

    window.deleteItem = function(type, id) {
        let confirmCallback = async () => {
            const result = await apiRequest(type, 'DELETE', { id });
            if (result.success) {
                window[type] = window[type].filter(i => i.id !== id);
                refreshUI();
                showMessage('Item deleted.');
            }
        };
        dom.confirmModal.querySelector('#confirm-text').textContent = "Are you sure?";
        dom.confirmModal.style.display = 'flex';
        document.getElementById('confirm-ok-btn').onclick = () => { confirmCallback(); dom.confirmModal.style.display = 'none'; };
        document.getElementById('confirm-cancel-btn').onclick = () => { dom.confirmModal.style.display = 'none'; };
    };

    // --- STARTUP ---
    initialize();
});
