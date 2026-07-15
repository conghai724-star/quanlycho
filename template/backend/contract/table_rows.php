<?php if (!empty($contracts)): ?>
    <?php foreach ($contracts as $contract): ?>
        <?php 
            $warningBadge = '';
            $rowStyle = 'border-bottom: 1px solid var(--border-color);';
            
            // Logic cảnh báo hết hạn (3 tháng = 90 ngày, 2 tháng = 60 ngày, 1 tháng = 30 ngày)
            if ($contract['status_code'] === 'active') {
                $days = (int)$contract['days_remaining'];
                if ($days <= 30) {
                    $warningBadge = '<span class="status status-red" style="font-weight: 600;">1 Tháng</span>';
                    $rowStyle = 'border-bottom: 1px solid var(--border-color); background-color: rgba(211, 47, 47, 0.05); border-left: 3px solid var(--red);';
                } elseif ($days <= 60) {
                    $warningBadge = '<span class="status status-orange" style="font-weight: 600; color: #e65100; background-color: #ffe0b2;">2 Tháng</span>';
                } elseif ($days <= 90) {
                    $warningBadge = '<span class="status status-yellow" style="font-weight: 600; color: #f57f17; background-color: #fffde7;">3 Tháng</span>';
                }
            }

            // Đếm số lượng phụ lục từ CSDL
            $db = database::getInstance();
            $appCountRes = $db->selectOne("SELECT COUNT(*) as count FROM contract_appendices WHERE contract_id = :id", ['id' => $contract['id']]);
            $appCount = $appCountRes['count'] ?? 0;
        ?>
        <tr style="<?php echo $rowStyle; ?>">
            <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                <?php echo htmlspecialchars($contract['contract_number']); ?>
            </td>
            <td style="padding: 14px 16px; font-weight: 500;">
                <?php echo htmlspecialchars($contract['name']); ?>
            </td>
            <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                <?php echo htmlspecialchars($contract['trader_name']); ?><br>
                <small style="color: var(--text-muted); font-size: 11px; font-weight: normal;"><?php echo htmlspecialchars($contract['trader_phone']); ?></small>
            </td>
            <td class="cell-mono" style="padding: 14px 16px; font-weight: 600;">
                <?php echo htmlspecialchars($contract['stall_code']); ?>
            </td>
            <td style="padding: 14px 16px; color: var(--text-muted);">
                <?php echo date('d/m/Y', strtotime($contract['start_date'])); ?>
            </td>
            <td style="padding: 14px 16px; color: <?php echo ($contract['status_code'] === 'expired') ? 'var(--red)' : 'var(--text-muted)'; ?>; font-weight: <?php echo ($contract['status_code'] === 'expired') ? '600' : 'normal'; ?>;">
                <?php echo date('d/m/Y', strtotime($contract['end_date'])); ?>
            </td>
            <td style="padding: 14px 16px; text-align: center;">
                <?php echo $warningBadge ?: '<span style="color: var(--text-muted); font-size: 11px;">-</span>'; ?>
            </td>
            <td style="padding: 14px 16px; font-weight: 600; color: var(--primary);">
                <?php echo number_format($contract['price'], 0, ',', '.'); ?> đ
            </td>
            <td style="padding: 14px 16px; color: var(--text-muted);">
                <?php echo number_format($contract['deposit'], 0, ',', '.'); ?> đ
            </td>
            <td style="padding: 14px 16px; text-align: center;">
                <?php if (!empty($contract['contract_file'])): ?>
                    <a href="<?php echo BASE_URL . 'uploads/contracts/' . htmlspecialchars($contract['contract_file']); ?>" target="_blank" style="color: var(--red); font-size: 16px;" title="Tải file PDF hợp đồng">
                        <i class="fa-solid fa-file-pdf"></i>
                    </a>
                <?php else: ?>
                    <span style="color: var(--text-muted); font-size: 11px;">N/A</span>
                <?php endif; ?>
            </td>
            <td style="padding: 14px 16px; text-align: center;">
                <button class="btn btn-ghost btn-sm" onclick="App.contract.viewAppendices(<?php echo $contract['id']; ?>, '<?php echo htmlspecialchars($contract['contract_number']); ?>')" style="padding: 4px; color: var(--primary); display: inline-flex; align-items: center; gap: 4px;" title="Xem phụ lục hợp đồng">
                    <i class="fa-solid fa-paperclip"></i> (<?php echo $appCount; ?>)
                </button>
            </td>
            <td style="padding: 14px 16px;">
                <span class="status <?php echo htmlspecialchars($contract['color_class'] ?: 'status-gray'); ?>">
                    <?php echo htmlspecialchars($contract['status_name']); ?>
                </span>
            </td>
            <td style="padding: 14px 16px; text-align: right;">
                <div style="display: flex; justify-content: flex-end; gap: 4px;">
                    <!-- Gia hạn hợp đồng (Chỉ khi chưa bị xóa/chưa thanh lý) -->
                    <?php if ($contract['status_code'] !== 'liquidated' && $contract['status_code'] !== 'terminated'): ?>
                        <button class="btn btn-outline btn-sm" onclick="App.contract.renewContract(<?php echo $contract['id']; ?>, '<?php echo htmlspecialchars($contract['contract_number']); ?>', '<?php echo $contract['end_date']; ?>')" style="padding: 4px 6px; color: var(--primary);" title="Gia hạn">
                            <i class="fa-solid fa-calendar-plus"></i>
                        </button>
                        <!-- Thanh lý / Chấm dứt -->
                        <button class="btn btn-outline btn-sm" onclick="App.contract.liquidateContract(<?php echo $contract['id']; ?>, '<?php echo htmlspecialchars($contract['contract_number']); ?>')" style="padding: 4px 6px; color: var(--orange);" title="Thanh lý">
                            <i class="fa-solid fa-file-contract"></i>
                        </button>
                        <button class="btn btn-outline btn-sm" onclick="App.contract.terminateContract(<?php echo $contract['id']; ?>, '<?php echo htmlspecialchars($contract['contract_number']); ?>')" style="padding: 4px 6px; color: var(--red);" title="Chấm dứt trước hạn">
                            <i class="fa-solid fa-ban"></i>
                        </button>
                    <?php endif; ?>
                    <!-- Xóa mềm -->
                    <button class="btn btn-outline btn-sm" onclick="App.contract.deleteContract(<?php echo $contract['id']; ?>, '<?php echo htmlspecialchars($contract['contract_number']); ?>')" style="padding: 4px 6px; color: var(--text-muted);" title="Xóa mềm">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="13" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu hợp đồng.</td>
    </tr>
<?php endif; ?>
