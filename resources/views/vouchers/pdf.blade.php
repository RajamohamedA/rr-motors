@include('vouchers.print', [
'voucher' => $voucher,
'paper' => $paper ?? 'a4',
'pdfMode' => true,
])