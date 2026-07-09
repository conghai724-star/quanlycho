<?php
if (!empty($traders)):
    foreach ($traders as $index => $trader):
        $debtVal = (float)($trader['total_debt'] ?? 0);
        $hasCert = true; // Hệ thống ATTP mặc định hiển thị
?>
        <tr style="border-bottom: 1px solid var(--border-color);" data-trader-row="<?php echo $trader['id']; ?>">
            <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                <?php echo htmlspecialchars($trader['trader_code']); ?>
            </td>
            <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                <?php echo htmlspecialchars($trader['fullname']); ?>
            </td>
            <td style="padding: 14px 16px; color: var(--text-muted);">
                <?php echo htmlspecialchars($trader['phone']); ?>
            </td>
            <td style="padding: 14px 16px; color: var(--text-muted); font-family: monospace;">
                <?php echo htmlspecialchars($trader['cccd']); ?>
            </td>
            <td style="padding: 14px 16px; color: var(--text-muted); max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($trader['address']); ?>">
                <?php echo htmlspecialchars($trader['address']); ?>
            </td>
            <td style="padding: 14px 16px;">
                <span class="chip"><?php echo htmlspecialchars($trader['business_line_name'] ?: 'Chưa cập nhật'); ?></span>
            </td>
            <td style="padding: 14px 16px; font-weight: 600; color: <?php echo $debtVal > 0 ? 'var(--red)' : 'var(--text-muted)'; ?>;">
                <?php echo number_format($debtVal, 0, ',', '.') . ' đ'; ?>
            </td>
            <td style="padding: 14px 16px; text-align: center;">
                <?php 
                $files = [];
                if (!empty($trader['license_file'])) {
                    $decoded = json_decode($trader['license_file'], true);
                    $files = is_array($decoded) ? $decoded : [$trader['license_file']];
                }
                if (!empty($files)): 
                ?>
                    <div style="display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: wrap;">
                        <?php foreach ($files as $index => $f): ?>
                            <a href="<?php echo BASE_URL; ?>uploads/traders/<?php echo htmlspecialchars($f); ?>" target="_blank" class="btn btn-ghost btn-sm" style="padding: 4px; color: var(--primary); display: inline-flex;" title="Xem tài liệu <?php echo $index + 1; ?>">
                                <i class="fa-regular fa-file-pdf" style="font-size: 15px;"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <span style="color: var(--text-muted); font-size: 11px;">Chưa nộp</span>
                <?php endif; ?>
            </td>
            <td style="padding: 14px 16px;">
                <span class="status <?php echo htmlspecialchars($trader['color_class'] ?? 'status-gray'); ?>">
                    <?php echo htmlspecialchars($trader['status_name'] ?? 'Không rõ'); ?>
                </span>
            </td>
            <td style="padding: 14px 16px; text-align: right;">
                <div style="display: flex; justify-content: flex-end; gap: 6px;">
                    <a href="<?php echo BASE_URL; ?>admin/trader_edit/<?php echo $trader['id']; ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 11px; text-decoration: none;" title="Sửa">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <button class="btn btn-ghost btn-sm btn-open-delete-trader" data-trader-id="<?php echo $trader['id']; ?>" data-trader-name="<?php echo htmlspecialchars($trader['fullname']); ?>" data-url="<?php echo BASE_URL; ?>api/deleteTrader" style="padding: 4px 8px; font-size: 11px; color: #EA4335;" title="Xóa">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="10" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu tiểu thương.</td>
    </tr>
<?php endif; ?>
