(function () {
    function init() {
        const redirectUrl = window.BASE_URL + 'admin/traders';

        // Thêm / Sửa tiểu thương
        App.utils.handleFormSubmit('form-add-trader',  redirectUrl);
        App.utils.handleFormSubmit('form-edit-trader', redirectUrl);

        // Kiểm tra trùng mã tiểu thương (chỉ cho form thêm vì form sửa readonly)
        App.utils.initRealtimeUniqueCheck('trader_code', 'api/checkExists', {
            getParams: val => ({ type: 'trader_code', value: val }),
            message: 'Mã tiểu thương này đã tồn tại trên hệ thống.'
        });

        // Kiểm tra trùng số CCCD (cho cả form thêm và sửa)
        App.utils.initRealtimeUniqueCheck('cccd', 'api/checkExists', {
            getParams: val => ({ 
                type: 'cccd',
                value: val,
                exclude_id: document.querySelector('input[name="id"]')?.value || ''
            }),
            message: 'Số CCCD này đã tồn tại trên hệ thống.'
        });

        // Xóa tiểu thương
        App.utils.initDelete({
            btnClass:  'btn-open-delete-trader',
            idAttr:    'traderId',
            nameAttr:  'traderName',
            label:     'hồ sơ tiểu thương'
        });

        // Lọc danh sách tiểu thương qua AJAX
        App.utils.initFilterFormAjax({
            buttonId:       'btn-filter-traders',
            tbodyId:        'table-body-traders',
            totalId:        'filter-total-traders',
            exportExcelId:  'btn-export-excel-traders',
            exportPdfId:    'btn-export-pdf-traders',
            apiUrl:         'api/filterTraders',
            pagePath:       'admin/traders',
            exportExcelPath:'admin/trader_export_excel',
            exportPdfPath:  'admin/trader_export_pdf'
        });
    }

    // Thăm dò an toàn: đợi App và Swal sẵn sàng
    function safeInit() {
        if (typeof App === 'undefined' || typeof Swal === 'undefined') {
            setTimeout(safeInit, 50);
            return;
        }
        init();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', safeInit);
    } else {
        safeInit();
    }
})();
