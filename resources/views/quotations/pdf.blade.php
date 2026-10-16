{{-- Reuse the quotation layout for matching A4/A5 PDF output. --}}
@include('quotations.print', [
'quotation' => $quotation,
'paper' => $paper ?? 'a4',
'pdfMode' => true,
])