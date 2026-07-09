<?php
/**
 * Tạo file .xlsx thật sự (OOXML) bằng PHP ZipArchive + XML
 * Không cần thư viện ngoài, chỉ cần extension: zip, xml (có sẵn trong XAMPP)
 *
 * Cách dùng:
 *   SimpleXlsx::download('filename.xlsx', $headers, $rows);
 *
 * $headers = ['Cột 1', 'Cột 2', ...]          — mảng tên cột
 * $rows    = [['a', 'b'], ['c', 'd'], ...]     — mảng các dòng dữ liệu
 *   Số nguyên/float sẽ được xuất ra ô số (Excel tính toán được)
 *   Chuỗi sẽ được xuất ra ô text
 */
class SimpleXlsx {

    /**
     * Tạo và gửi file xlsx ra trình duyệt (tải xuống ngay)
     * @param string $filename  Tên file (phải có đuôi .xlsx)
     * @param array  $headers   Danh sách tên cột (hàng tiêu đề)
     * @param array  $rows      Danh sách các dòng dữ liệu
     */
    public static function download(string $filename, array $headers, array $rows): void {
        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');

        $zip = new ZipArchive();
        $zip->open($tmpFile, ZipArchive::OVERWRITE);

        // --- Shared strings (tất cả chuỗi văn bản dùng chung) ---
        $strings = [];
        $strIndex = [];

        $addString = function(string $s) use (&$strings, &$strIndex): int {
            if (!isset($strIndex[$s])) {
                $strIndex[$s] = count($strings);
                $strings[] = $s;
            }
            return $strIndex[$s];
        };

        // --- Sinh nội dung sheet ---
        $sheetRows = '';

        // Hàng tiêu đề
        $sheetRows .= '<row r="1">';
        foreach ($headers as $ci => $h) {
            $col = self::colLetter($ci) . '1';
            $si  = $addString((string)$h);
            $sheetRows .= "<c r=\"$col\" t=\"s\"><v>$si</v></c>";
        }
        $sheetRows .= '</row>';

        // Hàng dữ liệu
        foreach ($rows as $ri => $row) {
            $rowNum = $ri + 2;
            $sheetRows .= "<row r=\"$rowNum\">";
            foreach ($row as $ci => $cell) {
                $col = self::colLetter($ci) . $rowNum;
                if (is_int($cell) || is_float($cell)) {
                    // Ô số — không cần shared strings
                    $sheetRows .= "<c r=\"$col\"><v>$cell</v></c>";
                } else {
                    $si = $addString((string)$cell);
                    $sheetRows .= "<c r=\"$col\" t=\"s\"><v>$si</v></c>";
                }
            }
            $sheetRows .= '</row>';
        }

        // --- Shared strings XML ---
        $ssCnt = count($strings);
        $ssXml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>"
               . "<sst xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\""
               . " count=\"$ssCnt\" uniqueCount=\"$ssCnt\">";
        foreach ($strings as $s) {
            $ssXml .= '<si><t xml:space="preserve">' . htmlspecialchars($s, ENT_XML1, 'UTF-8') . '</t></si>';
        }
        $ssXml .= '</sst>';

        // --- Sheet XML ---
        $sheetXml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>"
                  . "<worksheet xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\">"
                  . "<sheetData>$sheetRows</sheetData></worksheet>";

        // --- Styles XML (tối thiểu để Excel không báo lỗi) ---
        $stylesXml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>"
                   . "<styleSheet xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\">"
                   . "<fonts count=\"1\"><font><sz val=\"11\"/><name val=\"Calibri\"/></font></fonts>"
                   . "<fills count=\"2\"><fill><patternFill patternType=\"none\"/></fill>"
                   . "<fill><patternFill patternType=\"gray125\"/></fill></fills>"
                   . "<borders count=\"1\"><border><left/><right/><top/><bottom/><diagonal/></border></borders>"
                   . "<cellStyleXfs count=\"1\"><xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" borderId=\"0\"/></cellStyleXfs>"
                   . "<cellXfs count=\"1\"><xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" borderId=\"0\" xfId=\"0\"/></cellXfs>"
                   . "</styleSheet>";

        // --- Workbook XML ---
        $workbookXml = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>"
                     . "<workbook xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\""
                     . " xmlns:r=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships\">"
                     . "<sheets><sheet name=\"Sheet1\" sheetId=\"1\" r:id=\"rId1\"/></sheets></workbook>";

        // --- Relationships ---
        $wbRels = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>"
                . "<Relationships xmlns=\"http://schemas.openxmlformats.org/package/2006/relationships\">"
                . "<Relationship Id=\"rId1\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\""
                . " Target=\"worksheets/sheet1.xml\"/>"
                . "<Relationship Id=\"rId2\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings\""
                . " Target=\"sharedStrings.xml\"/>"
                . "<Relationship Id=\"rId3\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles\""
                . " Target=\"styles.xml\"/>"
                . "</Relationships>";

        $rootRels = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>"
                  . "<Relationships xmlns=\"http://schemas.openxmlformats.org/package/2006/relationships\">"
                  . "<Relationship Id=\"rId1\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument\""
                  . " Target=\"xl/workbook.xml\"/>"
                  . "</Relationships>";

        $contentTypes = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>"
                      . "<Types xmlns=\"http://schemas.openxmlformats.org/package/2006/content-types\">"
                      . "<Default Extension=\"rels\" ContentType=\"application/vnd.openxmlformats-package.relationships+xml\"/>"
                      . "<Default Extension=\"xml\" ContentType=\"application/xml\"/>"
                      . "<Override PartName=\"/xl/workbook.xml\""
                      . " ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml\"/>"
                      . "<Override PartName=\"/xl/worksheets/sheet1.xml\""
                      . " ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>"
                      . "<Override PartName=\"/xl/sharedStrings.xml\""
                      . " ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml\"/>"
                      . "<Override PartName=\"/xl/styles.xml\""
                      . " ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml\"/>"
                      . "</Types>";

        // --- Ghi vào ZIP ---
        $zip->addFromString('[Content_Types].xml',              $contentTypes);
        $zip->addFromString('_rels/.rels',                      $rootRels);
        $zip->addFromString('xl/workbook.xml',                  $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels',       $wbRels);
        $zip->addFromString('xl/worksheets/sheet1.xml',         $sheetXml);
        $zip->addFromString('xl/sharedStrings.xml',             $ssXml);
        $zip->addFromString('xl/styles.xml',                    $stylesXml);
        $zip->close();

        // --- Gửi file ra trình duyệt ---
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: max-age=0');
        readfile($tmpFile);
        unlink($tmpFile);
        exit();
    }

    /** Chuyển chỉ số cột (0-based) sang chữ cái A, B, ..., Z, AA, AB, ... */
    private static function colLetter(int $index): string {
        $letter = '';
        $index++;
        while ($index > 0) {
            $rem = ($index - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $index = (int)(($index - $rem) / 26);
        }
        return $letter;
    }
}
