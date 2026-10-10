@include('sales_reports.print', [
'rows' => $rows,
'summary' => $summary,
'sales' => $sales,
'filters' => $filters,
'store' => $store,
'paper' => $paper ?? 'a4',
'pdfMode' => true,
'generatedAt' => $generatedAt,
])