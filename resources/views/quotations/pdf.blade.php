@include('quotations.print', [
'quotation' => $quotation,
'paper' => $paper ?? 'a4',
'pdfMode' => true,
])