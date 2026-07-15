<?php
Class pdf{
	/*
     * @Variables array
     * @access public
     */
    private static $instance;

    /**
     *
     * @constructor
     *
     * @access public
     *
     * @return void
     *
     */
    function __construct() {

    }
    public static function getInstance() {
        if (!self::$instance)
        {
            self::$instance = new pdf();
        }
        return self::$instance;
    }
	public function income($id)
	{
		require_once __DIR__ . '/vendor/autoload.php';
		global $db;
		$db->query("SELECT * FROM hicrm_templates WHERE id = 1");
		$html = $db->fetch_object(true)->template_html;
		$db->query("SELECT * FROM hicrm_incomes as i 
		LEFT JOIN hicrm_customers as c ON i.income_to = c.id
		WHERE i.id = '".$id."'");
		$income = $db->fetch_object(true);
		$mpdf = new \Mpdf\Mpdf();
		
		$db->query("SELECT * FROM  hicrm_income_details WHERE income_id = '".$id."'");
		$details = $db->fetch_object();
		$total = 0;
		$debit = array();
		$credit = array();
		foreach($details as $d)
		{
			$total += $d->income_amount;
			array_push($debit,$d->income_debit);
			array_push($credit,$d->income_credit);
		}
		$credit = array_unique($credit);
		$debit = array_unique($debit);
		
		$date = $income->income_created_date;
		$html = str_replace("{{INCOME_NO}}",$income->income_no,$html);
		$html = str_replace("{{BILL_DAY}}",date("d",strtotime($date)),$html);
		$html = str_replace("{{BILL_MONTH}}",date("m",strtotime($date)),$html);
		$html = str_replace("{{BILL_YEAR}}",date("Y",strtotime($date)),$html);
		$html = str_replace("{{INCOME_NOTE}}",$income->income_note,$html);
		$html = str_replace("{{INCOME_DOCUMENT}}",$income->income_document,$html);
		$html = str_replace("{{INCOME_NAME}}",$income->customer_name,$html);
		$html = str_replace("{{INCOME_ADDRESS}}",$income->customer_address,$html);
		$html = str_replace("{{INCOME_AMOUNT}}",number_format($total,0,',','.'),$html);
		$html = str_replace("{{INCOME_AMOUNT_TEXT}}",$this->convert_number_to_words($total).' đồng chẵn./.',$html);
		$html = str_replace("{{DEBIT_ACC}}",implode(',',$debit),$html);
		$html = str_replace("{{CREDIT_ACC}}",implode(',',$credit),$html);
		$mpdf->WriteHTML($html);
		$mpdf->Output();
		//$dompdf = new Dompdf();
	}
	public function convert_number_to_words($number) {
 
		$hyphen      = ' ';
		$conjunction = ' ';
		$separator   = ' ';
		$negative    = 'âm ';
		$decimal     = ' phẩy ';
		$one		 = 'mốt';
		$ten         = 'lẻ';
		$dictionary  = array(
		0                   => 'Không',
		1                   => 'Một',
		2                   => 'Hai',
		3                   => 'Ba',
		4                   => 'Bốn',
		5                   => 'Năm',
		6                   => 'Sáu',
		7                   => 'Bảy',
		8                   => 'Tám',
		9                   => 'Chín',
		10                  => 'Mười',
		11                  => 'Mười một',
		12                  => 'Mười hai',
		13                  => 'Mười ba',
		14                  => 'Mười bốn',
		15                  => 'Mười lăm',
		16                  => 'Mười sáu',
		17                  => 'Mười bảy',
		18                  => 'Mười tám',
		19                  => 'Mười chín',
		20                  => 'Hai mươi',
		30                  => 'Ba mươi',
		40                  => 'Bốn mươi',
		50                  => 'Năm mươi',
		60                  => 'Sáu mươi',
		70                  => 'Bảy mươi',
		80                  => 'Tám mươi',
		90                  => 'Chín mươi',
		100                 => 'trăm',
		1000                => 'nghìn',
		1000000             => 'triệu',
		1000000000          => 'tỷ',
		1000000000000       => 'nghìn tỷ',
		1000000000000000    => 'nghìn triệu triệu',
		1000000000000000000 => 'tỷ tỷ'
		);
		 
		if (!is_numeric($number)) {
			return false;
		}
		 
		// if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
		// 	// overflow
		// 	trigger_error(
		// 	'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
		// 	E_USER_WARNING
		// 	);
		// 	return false;
		// }
		 
		if ($number < 0) {
			return $negative . $this->convert_number_to_words(abs($number));
		}
		 
		$string = $fraction = null;
		 
		if (strpos($number, '.') !== false) {
			list($number, $fraction) = explode('.', $number);
		}
		 
		switch (true) {
			case $number < 21:
				$string = $dictionary[$number];
			break;
			case $number < 100:
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = $dictionary[$tens];
				if ($units) {
					$string .= strtolower( $hyphen . ($units==1?$one:$dictionary[$units]) );
				}
			break;
			case $number < 1000:
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
				if ($remainder) {
					$string .= strtolower( $conjunction . ($remainder<10?$ten.$hyphen:null) . $this->convert_number_to_words($remainder) );
				}
			break;
			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int) ($number / $baseUnit);
				$remainder = $number - ($numBaseUnits*$baseUnit);
				$string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
				if ($remainder) {
					$string .= strtolower( $remainder < 100 ? $conjunction : $separator );
					$string .= strtolower( $this->convert_number_to_words($remainder) );
				}
			break;
		}
		 
		if (null !== $fraction && is_numeric($fraction)) {
			$string .= $decimal;
			$words = array();
			foreach (str_split((string) $fraction) as $number) {
				$words[] = $dictionary[$number];
			}
			$string .= implode(' ', $words);
		}
		
		return $string;
	}
}