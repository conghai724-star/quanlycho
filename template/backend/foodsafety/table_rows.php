<?php if (!empty($certificates)): ?>
    <?php foreach ($certificates as $cert): ?>
        <?php 
            $warningBadge = '';
            $rowStyle = 'border-bottom: 1px solid var(--border-color);';
            
            // Phân loại badge cho doc_type
            $docTypeLabel = '';
            $docTypeClass = '';
            switch ($cert['doc_type']) {
                case 'ATTP':
                    $docTypeLabel = 'GCN ATTP';
                    $docTypeClass = 'status-blue';
                    break;
                case 'Health':
                    $docTypeLabel = 'Khám sức khỏe';
                    $docTypeClass = 'status-green';
                    break;
                case 'Training':
                    $docTypeLabel = 'Tập huấn ATTP';
                    $docTypeClass = 'status-orange';
                    break;
                default:
                    $docTypeLabel = htmlspecialchars($cert['doc_type']);
                    $docTypeClass = 'status-gray';
            }

            // Logic cảnh báo hết hạn (3 tháng = 90 ngày, 2 tháng = 60 ngày, 1 tháng = 30 ngày)
            if ($cert['status_code'] === 'valid') {
                $days = (int)$cert['days_remaining'];
                if ($days <= 0) {
                    $warningBadge = '<span class="status status-red" style="font-weight: 600;">Hết hạn</span>';
                    $rowStyle = 'border-bottom: 1px solid var(--border-color); background-color: rgba(211, 47, 47, 0.05); border-left: 3px solid var(--red);';
                } elseif ($days <= 30) {
                    $warningBadge = '<span class="status status-red" style="font-weight: 600;">1 Tháng</span>';
                    $rowStyle = 'border-bottom: 1px solid var(--border-color); background-color: rgba(211, 47, 47, 0.05); border-left: 3px solid var(--red);';
                } elseif ($days <= 60) {
                    $warningBadge = '<span class="status status-orange" style="font-weight: 600; color: #e65100; background-color: #ffe0b2;">2 Tháng</span>';
                } elseif ($days <= 90) {
                    $warningBadge = '<span class="status status-yellow" style="font-weight: 600; color: #f57f17; background-color: #fffde7;">3 Tháng</span>';
                }
            } elseif ($cert['status_code'] === 'expired') {
                $rowStyle = 'border-bottom: 1px solid var(--border-color); background-color: rgba(211, 47, 47, 0.05); border-left: 3px solid var(--red);';
            }
        ?>
        <tr style="<?php echo $rowStyle; ?>">
            <!-- Tiểu thương & Cơ sở -->
            <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                <?php echo htmlspecialchars($cert['trader_name']); ?><br>
                <small style="color: var(--text-muted); font-size: 11px; font-weight: normal;"><?php echo htmlspecialchars($cert['trader_phone']); ?></small>
            </td>
            <td style="padding: 14px 16px; color: var(--text-heading); font-weight: 500;">
                <?php echo htmlspecialchars($cert['shop_name'] ?: 'Chưa cập nhật'); ?>
            </td>
            <!-- Loại giấy tờ -->
            <td style="padding: 14px 16px;">
                <span class="status <?php echo $docTypeClass; ?>" style="font-weight: 600;">
                    <?php echo $docTypeLabel; ?>
                </span>
            </td>
            <!-- Thông tin giấy tờ -->
            <td style="padding: 14px 16px;">
                <div style="font-weight: 600; color: var(--text-heading);"><?php echo htmlspecialchars($cert['name']); ?></div>
                <small class="cell-mono" style="color: var(--text-muted); font-size: 11px;">Số: <?php echo htmlspecialchars($cert['doc_number']); ?></small>
            </td>
            <!-- Cơ quan cấp -->
            <td style="padding: 14px 16px; color: var(--text-muted);">
                <?php echo htmlspecialchars($cert['issuer'] ?: '-'); ?>
            </td>
            <!-- Ngày cấp -->
            <td style="padding: 14px 16px; color: var(--text-muted);">
                <?php echo date('d/m/Y', strtotime($cert['issue_date'])); ?>
            </td>
            <!-- Ngày hết hạn -->
            <td style="padding: 14px 16px; color: <?php echo ($cert['status_code'] === 'expired') ? 'var(--red)' : 'var(--text-muted)'; ?>; font-weight: <?php echo ($cert['status_code'] === 'expired') ? '600' : 'normal'; ?>;">
                <?php echo date('d/m/Y', strtotime($cert['expiry_date'])); ?>
            </td>
            <!-- Cảnh báo hạn -->
            <td style="padding: 14px 16px; text-align: center;">
                <?php echo $warningBadge ?: '<span style="color: var(--text-muted); font-size: 11px;">Còn hạn</span>'; ?>
            </td>
            <!-- File đính kèm -->
            <td style="padding: 14px 16px; text-align: center;">
                <?php if (!empty($cert['file'])): ?>
                    <?php 
                        $isPdf = strtolower(pathinfo($cert['file'], PATHINFO_EXTENSION)) === 'pdf';
                        $iconClass = $isPdf ? 'fa-solid fa-file-pdf' : 'fa-solid fa-image';
                        $iconColor = $isPdf ? 'var(--red)' : 'var(--primary)';
                    ?>
                    <a href="<?php echo BASE_URL . 'uploads/foodsafety/' . htmlspecialchars($cert['file']); ?>" target="_blank" style="color: <?php echo $iconColor; ?>; font-size: 16px;" title="Xem tài liệu đính kèm">
                        <i class="<?php echo $iconClass; ?>"></i>
                    </a>
                <?php else: ?>
                    <span style="color: var(--text-muted); font-size: 11px;">N/A</span>
                <?php endif; ?>
            </td>
            <!-- Trạng thái -->
            <td style="padding: 14px 16px;">
                <span class="status <?php echo htmlspecialchars($cert['color_class'] ?: 'status-gray'); ?>">
                    <?php echo htmlspecialchars($cert['status_name']); ?>
                </span>
            </td>
            <!-- Thao tác -->
            <td style="padding: 14px 16px; text-align: right;">
                <div style="display: flex; justify-content: flex-end; gap: 6px;">
                    <!-- Sửa -->
                    <a class="btn btn-outline btn-sm" href="<?php echo BASE_URL; ?>admin/foodsafety_edit?id=<?php echo $cert['id']; ?>" style="padding: 4px 6px; color: var(--primary); display: inline-flex;" title="Chỉnh sửa">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <!-- Xóa -->
                    <button class="btn btn-outline btn-sm" onclick="App.foodsafety.deleteCertificate(<?php echo $cert['id']; ?>, '<?php echo htmlspecialchars($cert['doc_number']); ?>')" style="padding: 4px 6px; color: var(--red);" title="Xóa mềm">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="11" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu giấy tờ vệ sinh ATTP.</td>
    </tr>
<?php endif; ?>
