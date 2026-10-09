@include('cash_bank_book.print', [
'rows' => $rows,
'accounts' => $accounts,
'filters' => $filters,
'summary' => $summary,
'store' => $store,
'paper' => $paper ?? 'a4',
'pdfMode' => true,
'generatedAt' => $generatedAt,
])