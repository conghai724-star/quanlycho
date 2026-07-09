(function () {
    function init() {
        const redirectUrl = window.BASE_URL + 'admin/foodsafety';

        // 1. Thêm chứng nhận mới
        App.utils.handleFormSubmit('form-add-certificate', redirectUrl);

        // 2. Cập nhật chứng nhận (Form trên trang chỉnh sửa riêng biệt)
        App.utils.handleFormSubmit('form-edit-certificate', redirectUrl);

        // 3. Lọc danh sách qua AJAX
        App.utils.initFilterFormAjax({
            buttonId:       'btn-filter-certificates',
            tbodyId:        'table-body-certificates',
            totalId:        'filter-total-certificates',
            apiUrl:         'api/filterCertificates',
            pagePath:       'admin/foodsafety'
        });
    }

    // 4. Các hàm thao tác (gắn vào window)
    window.switchTab = function(mode) {
        if (mode === 'docs') {
            document.getElementById('fs-docs').style.display = 'block';
            document.getElementById('fs-inspections').style.display = 'none';
        } else {
            document.getElementById('fs-docs').style.display = 'none';
            document.getElementById('fs-inspections').style.display = 'block';
        }
    };

    // Khởi tạo namespace cho tab radio trong template
    window.App = window.App || {};
    window.App.foodsafety = {
        switchTab: window.switchTab
    };

    window.deleteCertificate = function(id, docNumber) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        Swal.fire({
            title: 'Xóa hồ sơ giấy tờ?',
            text: `Bạn có chắc chắn muốn xóa hồ sơ giấy tờ số "${docNumber}"? Bản ghi này sẽ được xóa mềm khỏi hệ thống.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận xóa',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#ff5252',
            cancelButtonColor: '#a0aec0',
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id', id);
                fd.append('csrf_token', window.CSRF_TOKEN);

                App.utils.apiPost(window.BASE_URL + 'api/deleteCertificate', fd, {
                    onSuccess: () => {
                        App.alert.success('Thành công', 'Đã xóa hồ sơ giấy tờ thành công!');
                        // Reload data table
                        const btnFilter = document.getElementById('btn-filter-certificates');
                        if (btnFilter) btnFilter.click();
                    }
                });
            }
        });
    };

    // Khởi chạy khi DOM sẵn sàng
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
