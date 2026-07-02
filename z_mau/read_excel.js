import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

// Lấy __dirname trong ES module
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function run() {
    try {
        // Import động thư viện xlsx
        const XLSX_MODULE = await import('xlsx');
        const XLSX = XLSX_MODULE.readFile ? XLSX_MODULE : (XLSX_MODULE.default || XLSX_MODULE);
        
        const excelPath = path.join(__dirname, 'Danh_sach_chuc_nang_quan_ly_cho.xlsx');
        if (!fs.existsSync(excelPath)) {
            console.error('Không tìm thấy file Excel tại: ' + excelPath);
            process.exit(1);
        }

        const workbook = XLSX.readFile(excelPath);
        let markdownOutput = '# Danh sách chức năng quản lý chợ (Trích xuất đầy đủ)\n\n';

        workbook.SheetNames.forEach((sheetName) => {
            markdownOutput += `## Sheet: ${sheetName}\n\n`;
            const sheet = workbook.Sheets[sheetName];
            
            // Chuyển sheet sang dạng mảng 2 chiều
            const data = XLSX.utils.sheet_to_json(sheet, { header: 1 });
            
            if (data.length === 0) {
                markdownOutput += '*Sheet này không có dữ liệu.*\n\n';
                return;
            }

            // Tìm số lượng cột lớn nhất trong tất cả các dòng
            let maxCols = 0;
            data.forEach(row => {
                if (row.length > maxCols) maxCols = row.length;
            });

            // Tạo header tạm thời (Cột 1, Cột 2, ...) hoặc lấy từ dòng tiêu đề thực tế
            const headers = [];
            for (let c = 0; c < maxCols; c++) {
                headers.push(`Cột ${c + 1}`);
            }

            markdownOutput += '| ' + headers.join(' | ') + ' |\n';
            markdownOutput += '| ' + headers.map(() => '---').join(' | ') + ' |\n';

            // Ghi toàn bộ dữ liệu các dòng
            for (let i = 0; i < data.length; i++) {
                const row = data[i] || [];
                const cells = [];
                for (let c = 0; c < maxCols; c++) {
                    const val = row[c];
                    cells.push(String(val === undefined || val === null ? '' : val).trim().replace(/\n/g, '<br>'));
                }
                markdownOutput += '| ' + cells.join(' | ') + ' |\n';
            }
            markdownOutput += '\n';
        });

        const outputPath = path.join(__dirname, 'danh_sach_chuc_nang.md');
        fs.writeFileSync(outputPath, markdownOutput, 'utf8');
        console.log('Đã trích xuất dữ liệu Excel thành công ra file: ' + outputPath);
    } catch (e) {
        console.error('Lỗi khi đọc file Excel:', e);
        process.exit(1);
    }
}

run();
