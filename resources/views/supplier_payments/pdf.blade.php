@include('supplier_payments.print', [
'payment' => $payment,
'paper' => $paper ?? 'a4',
'pdfMode' => true,
])