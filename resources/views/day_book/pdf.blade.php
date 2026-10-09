@include('day_book.print', [
'rows' => $rows,
'summary' => $summary,
'filters' => $filters,
'expenses' => $expenses,
'store' => $store,
'paper' => $paper ?? 'a4',
'pdfMode' => true,
'generatedAt' => $generatedAt,
])