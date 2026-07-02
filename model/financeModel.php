<?php
/**
 * Model quản lý các hóa đơn dịch vụ, phiếu thu, phiếu chi (Finance)
 */
class financeModel {
    private $db;

    public function __construct() {
        $this->db = database::getInstance();
    }

    /* ==========================================================================
       1. Quản lý Chỉ số Điện Nước (Utility Readings)
       ========================================================================== */

    public function createUtilityReading($data) {
        $sql = "INSERT INTO utility_readings (stall_id, reading_date, electric_old, electric_new, water_old, water_new, created_by)
                VALUES (:stall_id, :reading_date, :electric_old, :electric_new, :water_old, :water_new, :created_by)";
        
        $params = [
            'stall_id'     => $data['stall_id'],
            'reading_date' => $data['reading_date'],
            'electric_old' => $data['electric_old'],
            'electric_new' => $data['electric_new'],
            'water_old'    => $data['water_old'],
            'water_new'    => $data['water_new'],
            'created_by'   => $data['created_by']
        ];

        return $this->db->query($sql, $params);
    }

    /* ==========================================================================
       2. Quản lý Hóa đơn (Bills)
       ========================================================================== */

    public function getBills($status = null) {
        $sql = "SELECT b.*, c.contract_number, t.fullname AS trader_name, s.stall_code
                FROM bills b
                LEFT JOIN contracts c ON b.contract_id = c.id
                LEFT JOIN traders t ON c.trader_id = t.id
                LEFT JOIN stalls s ON c.stall_id = s.id";
        
        $params = [];
        if ($status) {
            $sql .= " WHERE b.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY b.id DESC";
        return $this->db->select($sql, $params);
    }

    public function createBill($data) {
        $sql = "INSERT INTO bills (contract_id, bill_code, invoice_date, rent_amount, electric_amount, water_amount, service_amount, total_amount, paid_amount, status)
                VALUES (:contract_id, :bill_code, :invoice_date, :rent_amount, :electric_amount, :water_amount, :service_amount, :total_amount, :paid_amount, :status)";
        
        $params = [
            'contract_id'     => $data['contract_id'],
            'bill_code'       => $data['bill_code'],
            'invoice_date'    => $data['invoice_date'],
            'rent_amount'     => $data['rent_amount'],
            'electric_amount' => $data['electric_amount'],
            'water_amount'    => $data['water_amount'],
            'service_amount'  => $data['service_amount'],
            'total_amount'    => $data['total_amount'],
            'paid_amount'     => $data['paid_amount'] ?? 0,
            'status'          => $data['status'] ?? 'unpaid'
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    /* ==========================================================================
       3. Phiếu Thu - Phiếu Chi (Receipts & Payments)
       ========================================================================== */

    public function getTransactions($type = null) {
        $sql = "SELECT r.*, u.fullname AS creator_name 
                FROM receipts_payments r
                LEFT JOIN users u ON r.created_by = u.id";
        
        $params = [];
        if ($type) {
            $sql .= " WHERE r.type = :type";
            $params['type'] = $type;
        }

        $sql .= " ORDER BY r.transaction_date DESC, r.id DESC";
        return $this->db->select($sql, $params);
    }

    public function createTransaction($data) {
        $sql = "INSERT INTO receipts_payments (transaction_code, type, amount, transaction_date, category, note, reference_id, created_by)
                VALUES (:transaction_code, :type, :amount, :transaction_date, :category, :note, :reference_id, :created_by)";
        
        $params = [
            'transaction_code' => $data['transaction_code'],
            'type'             => $data['type'],
            'amount'           => $data['amount'],
            'transaction_date' => $data['transaction_date'],
            'category'         => $data['category'],
            'note'             => $data['note'] ?? null,
            'reference_id'     => $data['reference_id'] ?? null,
            'created_by'       => $data['created_by']
        ];

        try {
            $this->db->beginTransaction();
            
            // 1. Tạo giao dịch thu/chi
            $this->db->query($sql, $params);
            $transId = $this->db->lastInsertId();

            // 2. Nếu là phiếu thu cho hóa đơn, cập nhật số tiền đã trả của hóa đơn
            if ($data['type'] === 'receipt' && !empty($data['reference_id'])) {
                $billId = $data['reference_id'];
                $amount = $data['amount'];

                // Cập nhật số tiền thanh toán của hóa đơn
                $updateBillSql = "UPDATE bills 
                                  SET paid_amount = paid_amount + :amount 
                                  WHERE id = :bill_id";
                $this->db->query($updateBillSql, ['amount' => $amount, 'bill_id' => $billId]);

                // Kiểm tra trạng thái hóa đơn để cập nhật (đã trả hết hay trả một phần)
                $bill = $this->db->selectOne("SELECT total_amount, paid_amount FROM bills WHERE id = :id", ['id' => $billId]);
                if ($bill) {
                    $newStatus = 'partially_paid';
                    if ($bill['paid_amount'] >= $bill['total_amount']) {
                        $newStatus = 'paid';
                    }
                    $updateStatusSql = "UPDATE bills SET status = :status WHERE id = :id";
                    $this->db->query($updateStatusSql, ['status' => $newStatus, 'id' => $billId]);
                }
            }

            $this->db->commit();
            return $transId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
