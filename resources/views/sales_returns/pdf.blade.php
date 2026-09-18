@php
$paper = strtolower($paper ?? 'a4');
$paper = in_array($paper, ['a4', 'a5'], true) ? $paper : 'a4';
@endphp

@include('sales_returns.print', [
'salesReturn' => $salesReturn,
'paper' => $paper,
'pdfMode' => true,
])