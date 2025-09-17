<?php
session_start();
$is_logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ön Muhasebe Programı</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-100">

    <div id="login-wrapper" class="min-h-screen flex items-center justify-center <?php if ($is_logged_in) echo 'hidden'; ?>">
        <div id="login-screen" class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-sm">
            <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">Giriş Yap</h2>
            <form id="login-form" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Kullanıcı Adı</label>
                    <input type="text" id="username" name="username" class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Şifre</label>
                    <input type="password" id="password" name="password" class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Giriş
                </button>
            </form>
        </div>
    </div>

    <div id="main-app" class="<?php if (!$is_logged_in) echo 'hidden'; ?> bg-white rounded-2xl shadow-xl w-full max-w-5xl overflow-hidden flex flex-col h-[90vh] mx-auto my-8">

        <div class="bg-gray-800 text-white p-4 flex items-center justify-between">
            <h1 class="text-xl font-bold">Ön Muhasebe Yönetimi</h1>
            <div class="flex items-center space-x-4">
                <button id="excel-export-btn" class="px-3 py-1 bg-green-500 text-white rounded-md hover:bg-green-600">
                    Excel'e Aktar (.xlsx)
                </button>
                <button id="logout-btn" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600">
                    Çıkış Yap
                </button>
            </div>
        </div>

        <div id="tab-menu" class="flex-shrink-0 flex items-center bg-gray-200 border-b border-gray-300 overflow-x-auto">
            <button class="tab-btn active px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition-colors" data-tab="stock-tab">Stok Takip</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition-colors" data-tab="stock-out-tab">Stok Hareketleri</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition-colors" data-tab="cash-tab">Kasa Takip</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition-colors" data-tab="cari-tab">Cari Hesaplar</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition-colors" data-tab="invoice-tab">Fatura Yönetimi</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition-colors" data-tab="cheque-tab">Çek / Senet</button>
            <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 transition-colors" data-tab="reports-tab">Raporlar</button>
        </div>

        <div class="p-6 flex-grow overflow-y-auto">

            <div id="stock-tab" class="tab-content active">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Stok Takip</h2>
                <div class="bg-white p-4 rounded-xl shadow-md mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">Yeni Ürün Ekle</h3>
                    <div class="flex space-x-2">
                        <input type="text" id="product-name" placeholder="Ürün Adı" class="px-3 py-2 border rounded-md w-1/2">
                        <input type="number" id="product-stock" placeholder="Stok Miktarı" class="px-3 py-2 border rounded-md w-1/4">
                        <button id="add-product-btn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Ekle</button>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">Barkoddan Ürün Ekle</h3>
                    <div class="flex space-x-2">
                        <input type="text" id="barcode-input" placeholder="Barkodu buraya okutun..." class="px-3 py-2 border rounded-md w-full">
                    </div>
                    <div id="barcode-details" class="hidden mt-4 bg-gray-50 p-3 rounded-md">
                        <p class="text-sm">**UBB / Ref No:** <span id="barcode-ubb" class="font-semibold"></span></p>
                        <p class="text-sm">**Lot No:** <span id="barcode-lot" class="font-semibold"></span></p>
                        <p class="text-sm">**SKT:** <span id="barcode-skt" class="font-semibold"></span></p>
                        <p class="text-sm mt-2">**Ürün Tanımı:** <span id="barcode-product-name" class="font-semibold"></span></p>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Ürün Tanım Listesi Yükle (.xlsx, .csv)</label>
                        <input type="file" id="product-list-file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept=".xlsx, .csv">
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md">
                    <h3 class="font-medium text-gray-700 mb-2">Stok Listesi</h3>
                    <div class="mb-4">
                        <input type="text" id="stock-filter-input" placeholder="Stok ara..." class="px-3 py-2 border rounded-md w-full">
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ürün Adı</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok Miktarı</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">UBB / Ref No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lot No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKT</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="stock-table-body" class="bg-white divide-y divide-gray-200">
                            </tbody>
                    </table>
                </div>
            </div>

            <div id="stock-out-tab" class="tab-content">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Stok Hareketleri</h2>

                <div class="bg-white p-4 rounded-xl shadow-md mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">Stok Çıkışı Yap</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                             <label class="block text-sm font-medium text-gray-700">Barkod Okutarak Ürün Bul</label>
                            <input type="text" id="barcode-input-issue" placeholder="Stoktan düşülecek ürünün barkodunu okutun..." class="mt-1 block w-full px-3 py-2 border rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ürün Seç</label>
                            <select id="stock-out-product" class="mt-1 block w-full px-3 py-2 border rounded-md">
                                <option value="">Ürün Seçiniz</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cari Hesap</label>
                            <select id="stock-out-cari" class="mt-1 block w-full px-3 py-2 border rounded-md">
                                <option value="">Cari Hesap Seçiniz</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Miktar</label>
                            <input type="number" id="stock-out-quantity" placeholder="Miktar" class="mt-1 block w-full px-3 py-2 border rounded-md">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Açıklama / Not</label>
                            <textarea id="stock-out-note" placeholder="Ek notlar" rows="2" class="mt-1 block w-full px-3 py-2 border rounded-md"></textarea>
                        </div>
                    </div>
                    <button id="send-stock-btn" class="w-full mt-4 px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Stoktan Düş</button>
                </div>

                <div class="bg-white p-4 rounded-xl shadow-md mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">Stok İadesi Al</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Barkod Okutarak Ürün Bul</label>
                            <input type="text" id="barcode-input-return" placeholder="İade alınacak ürünün barkodunu okutun..." class="mt-1 block w-full px-3 py-2 border rounded-md">
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-gray-700">Ürün Seç</label>
                            <select id="stock-return-product" class="mt-1 block w-full px-3 py-2 border rounded-md">
                                <option value="">Ürün Seçiniz</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">İade Alınan Cari</label>
                            <div class="flex">
                                <select id="stock-return-cari" class="mt-1 block w-full px-3 py-2 border rounded-md rounded-r-none">
                                    <option value="">Cari Hesap Seçiniz</option>
                                </select>
                                <button id="add-new-cari-return-btn" class="mt-1 px-3 bg-gray-200 hover:bg-gray-300 border border-l-0 rounded-r-md text-sm">+</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">İade Miktarı</label>
                            <input type="number" id="stock-return-quantity" placeholder="Miktar" class="mt-1 block w-full px-3 py-2 border rounded-md">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Açıklama</label>
                            <input type="text" id="stock-return-description" placeholder="İade açıklaması" class="mt-1 block w-full px-3 py-2 border rounded-md">
                        </div>
                    </div>
                    <button id="accept-return-btn" class="w-full mt-4 px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">İadeyi Onayla ve Stoğa Ekle</button>
                </div>

                <div class="bg-white p-4 rounded-xl shadow-md">
                    <h3 class="font-medium text-gray-700 mb-2">Stok Hareketleri</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hareket Tipi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ürün</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Miktar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cari Hesap</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Açıklama</th>
                            </tr>
                        </thead>
                        <tbody id="stock-movements-table-body" class="bg-white divide-y divide-gray-200">
                            </tbody>
                    </table>
                </div>
            </div>

            <div id="cash-tab" class="tab-content">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Kasa Takip</h2>
                <div class="bg-white p-4 rounded-xl shadow-md mb-4 flex items-center justify-between">
                    <span class="text-lg font-bold text-gray-700">Kasa Bakiyesi:</span>
                    <span id="cash-balance" class="text-2xl font-bold text-green-600">0.00 TL</span>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">Nakit İşlemi Ekle</h3>
                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <input type="text" id="cash-description" placeholder="Açıklama" class="px-3 py-2 border rounded-md">
                        <input type="date" id="cash-date" placeholder="Tarih" class="px-3 py-2 border rounded-md">
                        <input type="number" id="cash-amount" placeholder="Tutar" class="px-3 py-2 border rounded-md">
                        <select id="cash-type" class="px-3 py-2 border rounded-md">
                            <option value="income">Giriş</option>
                            <option value="expense">Çıkış</option>
                        </select>
                    </div>
                    <button id="add-cash-btn" class="w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Ekle</button>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md">
                    <h3 class="font-medium text-gray-700 mb-2">Kasa Hareketleri</h3>
                    <div class="mb-4">
                        <input type="text" id="cash-filter-input" placeholder="Kasa hareketleri ara..." class="px-3 py-2 border rounded-md w-full">
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Açıklama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tutar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlem Tipi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="cash-table-body" class="bg-white divide-y divide-gray-200">
                            </tbody>
                    </table>
                </div>
            </div>

            <div id="cari-tab" class="tab-content">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Cari Hesaplar</h2>
                <div class="bg-white p-4 rounded-xl shadow-md mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">Yeni Cari Ekle</h3>
                    <div class="flex space-x-2 mb-2">
                        <input type="text" id="cari-name" placeholder="Adı / Ünvanı" class="px-3 py-2 border rounded-md w-1/2">
                        <input type="text" id="cari-type" placeholder="Müşteri/Tedarikçi" class="px-3 py-2 border rounded-md w-1/4">
                        <button id="add-cari-btn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Ekle</button>
                    </div>
                    <div class="mt-4">
                        <input type="text" id="cari-filter-input" placeholder="Cari hesaba göre filtrele..." class="px-3 py-2 border rounded-md w-full">
                    </div>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md">
                    <h3 class="font-medium text-gray-700 mb-2">Cari Hesap Listesi</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adı / Ünvanı</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vergi No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bakiye</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="cari-table-body" class="bg-white divide-y divide-gray-200">
                            </tbody>
                    </table>
                </div>
            </div>

            <div id="invoice-tab" class="tab-content">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Fatura Yönetimi</h2>
                <div class="bg-white p-4 rounded-xl shadow-md mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">Yeni Fatura Ekle</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" id="invoice-number" placeholder="Fatura No" class="px-3 py-2 border rounded-md">
                        <input type="date" id="invoice-date" placeholder="Fatura Tarihi" class="px-3 py-2 border rounded-md">
                        <select id="invoice-cari" class="px-3 py-2 border rounded-md">
                           <option value="">Cari Hesap Seçiniz</option>
                        </select>
                        <input type="number" id="invoice-total" placeholder="Toplam Tutar" class="px-3 py-2 border rounded-md">
                        <select id="invoice-type" class="px-3 py-2 border rounded-md">
                            <option value="sale">Satış Faturası</option>
                            <option value="purchase">Alış Faturası</option>
                            <option value="return">İade Faturası</option>
                        </select>
                    </div>
                    <button id="add-invoice-btn" class="mt-4 w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Fatura Kaydet</button>
                    <button id="recognize-invoice-btn" class="mt-2 w-full px-4 py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600">Fatura Tanı (Gemini)</button>
                    <button id="batch-recognize-invoice-btn" class="mt-2 w-full px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Toplu Fatura Yükle</button>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md">
                    <h3 class="font-medium text-gray-700 mb-2">Fatura Listesi</h3>
                    <div class="mb-4">
                        <input type="text" id="invoice-filter-input" placeholder="Fatura ara..." class="px-3 py-2 border rounded-md w-full">
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fatura No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cari Hesap</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Toplam</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="invoice-table-body" class="bg-white divide-y divide-gray-200">
                            </tbody>
                    </table>
                </div>
            </div>

            <div id="cheque-tab" class="tab-content">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Çek / Senet</h2>
                <div class="bg-white p-4 rounded-xl shadow-md mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">Yeni Çek/Senet Ekle</h3>
                    <div class="grid grid-cols-2 gap-2 mt-4">
                        <input type="text" id="cheque-number" placeholder="Belge No" class="px-3 py-2 border rounded-md">
                        <input type="date" id="cheque-date" placeholder="Tarih" class="px-3 py-2 border rounded-md">
                        <select id="cheque-cari" class="px-3 py-2 border rounded-md">
                            <option value="">Cari Hesap Seçiniz</option>
                        </select>
                        <input type="number" id="cheque-amount" placeholder="Tutar" class="px-3 py-2 border rounded-md">
                        <select id="cheque-status" class="px-3 py-2 border rounded-md">
                            <option value="portfolio">Portföyde</option>
                            <option value="collected">Tahsil Edildi</option>
                            <option value="bounced">Karşılıksız</option>
                        </select>
                    </div>
                    <button id="add-cheque-btn" class="w-full mt-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Ekle</button>
                </div>
                <div class="bg-white p-4 rounded-xl shadow-md">
                    <h3 class="font-medium text-gray-700 mb-2">Çek / Senet Listesi</h3>
                       <div class="mb-4">
                            <input type="text" id="cheque-filter-input" placeholder="Çek/Senet ara..." class="px-3 py-2 border rounded-md w-full">
                        </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Belge No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cari Hesap</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tutar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durum</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="cheque-table-body" class="bg-white divide-y divide-gray-200">
                            </tbody>
                    </table>
                </div>
            </div>

            <div id="reports-tab" class="tab-content">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Raporlama ve Analiz</h2>
                <div class="bg-white p-4 rounded-xl shadow-md mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">Kasa Hareketleri Grafiği</h3>
                    <canvas id="cash-chart"></canvas>
                </div>
                <div id="cari-report-container" class="bg-white p-4 rounded-xl shadow-md">
                    <h3 class="font-medium text-gray-700 mb-2">Cari Hesap Raporu</h3>
                    <select id="cari-select" class="w-full px-3 py-2 border rounded-md mb-4">
                        <option value="">Cari Hesap Seçiniz</option>
                    </select>
                    <div id="cari-report-details" class="hidden">
                        <h4 class="text-lg font-semibold mb-2">İşlem Detayları</h4>
                        <p id="cari-report-balance" class="text-md font-bold text-gray-800 mb-4"></p>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Açıklama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tutar</th>
                                </tr>
                            </thead>
                            <tbody id="cari-report-body" class="bg-white divide-y divide-gray-200">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div id="message-modal" class="modal">
        <div class="modal-content">
            <span id="modal-close-btn" class="modal-close-btn">&times;</span>
            <p id="modal-text" class="text-gray-700 text-center"></p>
            <div class="mt-4 flex justify-center">
                <button id="modal-ok-btn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Tamam</button>
            </div>
        </div>
    </div>

    <div id="confirm-modal" class="modal">
        <div class="modal-content">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Onay</h3>
            <p id="confirm-text" class="text-gray-700 mb-4"></p>
            <div class="flex justify-center space-x-4">
                <button id="confirm-ok-btn" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">Evet, Sil</button>
                <button id="confirm-cancel-btn" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">İptal</button>
            </div>
        </div>
    </div>

    <div id="add-stock-confirm-modal" class="modal">
        <div class="modal-content">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Onay</h3>
            <p id="add-stock-confirm-text" class="text-gray-700 mb-4"></p>
            <div class="flex justify-center space-x-4">
                <button id="confirm-add-stock-ok-btn" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Evet, Ekle</button>
                <button id="confirm-add-stock-cancel-btn" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">İptal</button>
            </div>
        </div>
    </div>


    <div id="edit-modal" class="modal">
        <div class="modal-content relative">
            <span id="edit-modal-close-btn" class="modal-close-btn">&times;</span>
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Kaydı Düzenle</h3>
            <form id="edit-form" class="space-y-4">
                </form>
        </div>
    </div>

    <div id="recognize-modal" class="modal">
        <div class="modal-content relative">
            <span id="recognize-modal-close-btn" class="modal-close-btn">&times;</span>
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Fatura Tanıma (Gemini)</h3>
            <p class="text-gray-600 mb-4">Bir faturanın fotoğrafını veya PDF dosyasını yükleyerek bilgileri otomatik olarak okuyun.</p>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Google Gemini API Anahtarı</label>
                    <input type="password" id="gemini-api-key-input" placeholder="Gemini API anahtarınızı buraya girin" class="mt-1 block w-full text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fatura Dosyası Yükle</label>
                    <input type="file" id="invoice-file-input" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*, application/pdf" multiple>
                </div>
                <button id="process-invoice-btn" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Faturayı İşle
                </button>
                <div id="processing-loader" class="hidden flex justify-center items-center mt-4">
                    <div class="loader mr-2"></div>
                    <p class="text-gray-500 text-sm">Fatura işleniyor...</p>
                </div>
            </div>
            <div id="recognized-data" class="mt-4 hidden">
                <h4 class="font-semibold text-gray-800 mb-2">Tanınan Bilgiler:</h4>
                <p id="recognized-invoice-no" class="text-sm text-gray-600"></p>
                <p id="recognized-invoice-date" class="text-sm text-gray-600"></p>
                <p id="recognized-invoice-total" class="text-sm text-gray-600"></p>
                <p id="recognized-invoice-cari" class="text-sm text-gray-600"></p>
                <p id="recognized-invoice-tax-number" class="text-sm text-gray-600"></p>
                <p id="recognized-invoice-tax-office" class="text-sm text-gray-600"></p>
                <button id="fill-form-btn" class="mt-4 w-full px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Formu Doldur</button>
            </div>
        </div>
    </div>

    <div id="new-cari-modal" class="modal">
        <div class="modal-content relative">
            <span id="new-cari-modal-close-btn" class="modal-close-btn">&times;</span>
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Yeni Cari Hesap Ekle</h3>
            <p class="text-sm text-gray-600 mb-4" id="new-cari-name-text">Faturada tanınan cari hesap ("...") listede bulunamadı. Yeni bir cari hesap eklemek için lütfen vergi bilgilerini girin.</p>
            <form id="new-cari-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Vergi Kimlik Numarası (VKN)</label>
                    <input type="text" id="new-cari-tax-number" class="mt-1 block w-full px-3 py-2 border rounded-md" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Vergi Dairesi</label>
                    <input type="text" id="new-cari-tax-office" class="mt-1 block w-full px-3 py-2 border rounded-md">
                </div>
                <button type="submit" class="w-full mt-4 px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Kaydet ve Faturayı Ekle</button>
            </form>
        </div>
    </div>

    <div id="batch-invoice-modal" class="modal">
        <div class="modal-content relative">
            <span id="batch-invoice-modal-close-btn" class="modal-close-btn">&times;</span>
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Toplu Fatura Yükle</h3>
            <div class="space-y-4">
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Google Gemini API Anahtarı</label>
                    <input type="password" id="batch-gemini-api-key-input" placeholder="Gemini API anahtarınızı buraya girin" class="mt-1 block w-full text-sm">
                </div>
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Fatura Tipi</label>
                    <select id="batch-invoice-type" class="mt-1 block w-full px-3 py-2 border rounded-md">
                        <option value="purchase">Alış Faturası</option>
                        <option value="sale">Satış Faturası</option>
                        <option value="return">İade Faturası</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fatura Dosyaları Yükle</label>
                    <input type="file" id="batch-invoice-file-input" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" accept="image/*, application/pdf" multiple>
                </div>
                <button id="process-batch-invoice-btn" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    Yükle ve İşle
                </button>
            </div>
        </div>
    </div>


    <div id="cari-ekstre-modal" class="modal">
        <div class="modal-content relative max-w-2xl">
            <span id="cari-ekstre-modal-close-btn" class="modal-close-btn">&times;</span>
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Cari Hesap Ekstresi</h3>
            <div id="ekstre-cari-bilgi" class="bg-gray-100 p-4 rounded-md mb-4">
                <p id="ekstre-cari-ad" class="font-bold text-lg"></p>
                <p id="ekstre-cari-vkn" class="text-sm text-gray-600"></p>
                <p id="ekstre-cari-bakiye" class="text-md font-bold mt-2"></p>
            </div>
            <div id="ekstre-islemler" class="overflow-y-auto max-h-64">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarih</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Açıklama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tutar</th>
                        </tr>
                    </thead>
                    <tbody id="ekstre-body" class="bg-white divide-y divide-gray-200">
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end space-x-2">
                <button id="ekstre-pdf-btn" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">PDF Aktar</button>
                <button id="ekstre-xlsx-btn" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">XLSX Aktar</button>
                <button id="ekstre-csv-btn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">CSV Aktar</button>
            </div>
        </div>
    </div>
    <div id="notification" class="hidden"></div>
    <script src="js/app.js"></script>
</body>
</html>
