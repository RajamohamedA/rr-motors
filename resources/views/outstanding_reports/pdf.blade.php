@include('outstanding_reports.print', [
'rows' => $rows,
'filters' => $filters,
'summary' => $summary,
'store' => $store,
'paper' => $paper ?? 'a4',
'pdfMode' => true,
'generatedAt' => $generatedAt,
])