@include('purchase_reports.print', [
'rows' => $rows,
'summary' => $summary,
'purchases' => $purchases,
'filters' => $filters,
'store' => $store,
'paper' => $paper ?? 'a4',
'pdfMode' => true,
'generatedAt' => $generatedAt,
])