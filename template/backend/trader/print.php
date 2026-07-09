<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($title); ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            margin: 0;
            font-size: 11.5px;
            line-height: 1.5;
        }
        /* ── Header ── */
        .header { text-align: center; margin-bottom: 18px; }
        .org  { font-size: 13px; font-weight: bold; text-transform: uppercase; margin: 0 0 2px; }
        .sub  { font-size: 10.5px; color: #555; margin: 0 0 12px; }
        .report-title { font-size: 16px; font-weight: bold; text-transform: uppercase; margin: 0 0 4px; }
        .meta { font-size: 10.5px; color: #444; margin: 0; }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td {
            border: 1px solid #999;
            padding: 6px 7px;
            text-align: left;
            vertical-align: middle;
        }
        th {
            background-color: #e8edf2;
            font-weight: bold;
            font-size: 11px;
            white-space: nowrap;
        }
        td { white-space: nowrap; }
        /* Chỉ cho phép cột dài tự wrap */
        td.wrap { white-space: normal; }

        tr:nth-child(even) td { background: #f7f9fb; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .total-row td { font-weight: bold; background: #e8edf2; }

        /* ── Chữ ký — dùng table thay flex (mPDF không hỗ trợ flex) ── */
        .footer-table { width: 100%; margin-top: 40px; }
        .footer-table td { border: none; text-align: center; vertical-align: top; width: 50%; }
        .sig-label  { font-weight: bold; font-size: 12px; margin-bottom: 2px; }
        .sig-note   { font-size: 10px; color: #555; }
        .sig-space  { height: 64px; }
        .sig-line   { font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="org">Ban Quản Lý Chợ Smart</p>
        <p class="sub">Địa chỉ: Số 1 Đại Cồ Việt, Hai Bà Trưng, Hà Nội</p>
        <p class="report-title"><?php echo htmlspecialchars($title); ?></p>
        <p class="meta">
            Ngày lập: <?php echo date('d/m/Y H:i'); ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;Tổng số: <strong><?php echo count($traders); ?></strong> tiểu thương
            <?php if (!empty($filterDesc)): ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;Bộ lọc: <strong><?php echo htmlspecialchars($filterDesc); ?></strong>
            <?php endif; ?>
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 4%;">STT</th>
                <th style="width: 9%;">Mã TT</th>
                <th class="wrap" style="width: 16%;">Họ và tên</th>
                <th style="width: 11%;">Điện thoại</th>
                <th style="width: 13%;">Số CCCD</th>
                <th class="wrap" style="width: 15%;">Ngành hàng</th>
                <th style="width: 12%;">Trạng thái</th>
                <th class="text-right" style="width: 12%;">Công nợ (đ)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($traders)): ?>
                <?php $totalDebt = 0; ?>
                <?php foreach ($traders as $index => $trader): ?>
                    <?php $debt = (int)($trader['total_debt'] ?? 0); $totalDebt += $debt; ?>
                    <tr>
                        <td class="text-center"><?php echo $index + 1; ?></td>
                        <td><strong><?php echo htmlspecialchars($trader['trader_code']); ?></strong></td>
                        <td class="wrap"><?php echo htmlspecialchars($trader['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($trader['phone']); ?></td>
                        <td><?php echo htmlspecialchars($trader['cccd']); ?></td>
                        <td class="wrap"><?php echo htmlspecialchars($trader['business_line_name'] ?: 'Chưa cập nhật'); ?></td>
                        <td><?php echo htmlspecialchars($trader['status_name'] ?? 'Không rõ'); ?></td>
                        <td class="text-right" style="<?php echo $debt > 0 ? 'color:#c0392b;font-weight:bold;' : ''; ?>">
                            <?php echo number_format($debt, 0, ',', '.'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="7" class="text-right">Tổng công nợ:</td>
                    <td class="text-right"><?php echo number_format($totalDebt, 0, ',', '.'); ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center" style="color:#888; padding:16px;">Không có dữ liệu tiểu thương phù hợp.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Dùng table thay flex — mPDF không hỗ trợ flexbox -->
    <table class="footer-table">
        <tr>
            <td>
                <p class="sig-label">Người lập báo cáo</p>
                <p class="sig-note">(Ký và ghi rõ họ tên)</p>
                <div class="sig-space"></div>
                <p class="sig-line">..........................</p>
            </td>
            <td>
                <p class="sig-label">Trưởng Ban Quản Lý</p>
                <p class="sig-note">(Ký tên và đóng dấu)</p>
                <div class="sig-space"></div>
                <p class="sig-line">..........................</p>
            </td>
        </tr>
    </table>

</body>
</html>
