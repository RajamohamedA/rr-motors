@include('supplier_ledgers.print', [
'supplier' => $supplier,
'store' => $store,
'entries' => $entries,
'filters' => $filters,
'summary' => $summary,
'paper' => $paper ?? 'a4',
'pdfMode' => true,
])