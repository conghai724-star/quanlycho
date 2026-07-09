USE `quanly_cho`;

-- 1. Thêm cột block (Dãy) và lot (Lô) vào bảng stalls nếu chưa có
ALTER TABLE `stalls` 
ADD COLUMN `block` VARCHAR(50) NULL AFTER `stall_code`,
ADD COLUMN `lot` VARCHAR(50) NULL AFTER `block`;

-- 2. Cập nhật thông tin dãy và lô cho dữ liệu mẫu hiện có
UPDATE `stalls` SET `block` = 'Dãy A', `lot` = '01' WHERE `stall_code` = 'SẠP-A01';
UPDATE `stalls` SET `block` = 'Dãy A', `lot` = '02' WHERE `stall_code` = 'SẠP-A02';
UPDATE `stalls` SET `block` = 'Dãy A', `lot` = '03' WHERE `stall_code` = 'SẠP-A03';
UPDATE `stalls` SET `block` = 'Dãy A', `lot` = '04' WHERE `stall_code` = 'SẠP-A04';
UPDATE `stalls` SET `block` = 'Dãy B', `lot` = '01' WHERE `stall_code` = 'SẠP-B01';
UPDATE `stalls` SET `block` = 'Dãy B', `lot` = '02' WHERE `stall_code` = 'SẠP-B02';
UPDATE `stalls` SET `block` = 'Dãy B', `lot` = '03' WHERE `stall_code` = 'SẠP-B03';
UPDATE `stalls` SET `block` = 'Dãy C', `lot` = '01' WHERE `stall_code` = 'SẠP-C01';
