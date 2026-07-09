<?php
if (!empty($stalls)):
    foreach ($stalls as $stall):
?>
        <tr style="border-bottom: 1px solid var(--border-color);" data-stall-row="<?php echo $stall['id']; ?>">
            <!-- Mã sạp -->
            <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                <?php echo htmlspecialchars($stall['stall_code']); ?>
            </td>
            <!-- Phân khu -->
            <td style="padding: 14px 16px;">
                <span class="chip"><?php echo htmlspecialchars($stall['area_name']); ?></span>
            </td>
            <!-- Vị trí cụ thể (Dãy / Lô) -->
            <td style="padding: 14px 16px; color: var(--text-muted);">
                <?php 
                $location = [];
                if (!empty($stall['block'])) $location[] = htmlspecialchars($stall['block']);
                if (!empty($stall['lot'])) $location[] = 'Lô ' . htmlspecialchars($stall['lot']);
                echo !empty($location) ? implode(', ', $location) : 'Chưa cập nhật';
                ?>
            </td>
            <!-- Diện tích -->
            <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                <?php echo htmlspecialchars($stall['area_size']); ?> m²
            </td>
            <!-- Đơn giá thuê -->
            <td style="padding: 14px 16px; font-weight: 600; color: var(--primary);">
                <?php echo number_format($stall['base_price'], 0, ',', '.'); ?> đ
            </td>
            <!-- Tiểu thương -->
            <td style="padding: 14px 16px;">
                <?php if ($stall['status'] === 'rented' && !empty($stall['trader_name'])): ?>
                    <div style="font-weight: 600; color: var(--text-heading);">
                        <?php echo htmlspecialchars($stall['trader_name']); ?>
                    </div>
                    <?php if (!empty($stall['business_line_name'])): ?>
                        <small style="color: var(--text-muted); font-size: 11px;">
                            (<?php echo htmlspecialchars($stall['business_line_name']); ?>)
                        </small>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color: var(--text-muted); font-style: italic;">Chưa có</span>
                <?php endif; ?>
            </td>
            <!-- Trạng thái -->
            <td style="padding: 14px 16px;">
                <span class="status <?php echo htmlspecialchars($stall['color_class'] ?? 'status-gray'); ?>">
                    <?php echo htmlspecialchars($stall['status_name'] ?? 'Không rõ'); ?>
                </span>
            </td>
            <!-- Thao tác -->
            <td style="padding: 14px 16px; text-align: right;">
                <div style="display: flex; justify-content: flex-end; gap: 6px; align-items: center;">
                    <!-- Nút Gán / Chuyển sạp nhanh -->
                    <?php if ($stall['status'] === 'empty'): ?>
                        <button class="btn btn-ghost btn-sm btn-assign-stall-quick" 
                                data-stall-id="<?php echo $stall['id']; ?>" 
                                data-stall-code="<?php echo htmlspecialchars($stall['stall_code']); ?>" 
                                style="padding: 4px; color: var(--primary);" 
                                title="Gán sạp cho tiểu thương">
                            <i class="fa-solid fa-user-plus" style="font-size: 13px;"></i>
                        </button>
                    <?php elseif ($stall['status'] === 'rented'): ?>
                        <button class="btn btn-ghost btn-sm btn-transfer-stall-quick" 
                                data-stall-id="<?php echo $stall['id']; ?>" 
                                data-stall-code="<?php echo htmlspecialchars($stall['stall_code']); ?>" 
                                data-trader-name="<?php echo htmlspecialchars($stall['trader_name'] ?? ''); ?>" 
                                style="padding: 4px; color: var(--blue);" 
                                title="Chuyển đổi sạp">
                            <i class="fa-solid fa-right-left" style="font-size: 13px;"></i>
                        </button>
                    <?php endif; ?>

                    <!-- Nút Sửa -->
                    <a href="<?php echo BASE_URL; ?>admin/stall_edit/<?php echo $stall['id']; ?>" 
                       class="btn btn-outline btn-sm" 
                       style="padding: 4px 8px; font-size: 11px; text-decoration: none;" 
                       title="Chỉnh sửa">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>

                    <!-- Nút Xóa -->
                    <button class="btn btn-ghost btn-sm btn-open-delete-stall" 
                            data-stall-id="<?php echo $stall['id']; ?>" 
                            data-stall-code="<?php echo htmlspecialchars($stall['stall_code']); ?>" 
                            data-url="<?php echo BASE_URL; ?>api/deleteStall" 
                            style="padding: 4px 8px; font-size: 11px; color: #EA4335;" 
                            title="Xóa">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </td>
        </tr>
<?php 
    endforeach;
else: 
?>
    <tr>
        <td colspan="8" style="padding: 30px; text-align: center; color: var(--text-muted);">
            Không có dữ liệu sạp.
        </td>
    </tr>
<?php endif; ?>
