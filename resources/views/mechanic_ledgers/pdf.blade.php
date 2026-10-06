@include('mechanic_ledgers.print', [
'company' => $company,
'store' => $store,
'mechanic' => $mechanic,
'entries' => $entries,
'filters' => $filters,
'summary' => $summary,
'paper' => $paper ?? ($filters['paper'] ?? 'a4'),
'pdfMode' => true,
])