<?php

namespace App\Services;

class XlsxParser
{
    public static function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File XLSX tidak ditemukan: $filePath");
        }

        $script = <<<'PY'
import sys, json, zipfile, xml.etree.ElementTree as ET

file_path = sys.argv[1]
z = zipfile.ZipFile(file_path)

sst_xml = z.read('xl/sharedStrings.xml') if 'xl/sharedStrings.xml' in z.namelist() else None
strings = []
if sst_xml:
    sst = ET.fromstring(sst_xml)
    for si in sst.findall('{http://schemas.openxmlformats.org/spreadsheetml/2006/main}si'):
        t = si.find('{http://schemas.openxmlformats.org/spreadsheetml/2006/main}t')
        if t is not None and t.text:
            strings.append(t.text)
        else:
            # Check for multiple r elements in rich text
            t_parts = [r.find('{http://schemas.openxmlformats.org/spreadsheetml/2006/main}t') for r in si.findall('{http://schemas.openxmlformats.org/spreadsheetml/2006/main}r')]
            text = "".join([tp.text for tp in t_parts if tp is not None and tp.text])
            strings.append(text)

sheet_xml = z.read('xl/worksheets/sheet1.xml')
sheet = ET.fromstring(sheet_xml)

rows = []
for r in sheet.findall('.//{http://schemas.openxmlformats.org/spreadsheetml/2006/main}row'):
    row_vals = []
    for c in r.findall('{http://schemas.openxmlformats.org/spreadsheetml/2006/main}c'):
        t = c.attrib.get('t')
        v = c.find('{http://schemas.openxmlformats.org/spreadsheetml/2006/main}v')
        val = v.text if v is not None else ''
        if t == 's' and val.isdigit() and int(val) < len(strings):
            val = strings[int(val)]
        row_vals.append(val)
    if any(row_vals):
        rows.append(row_vals)

print(json.dumps(rows))
PY;

        $tempPy = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'xlsx_parser_' . uniqid() . '.py';
        file_put_contents($tempPy, $script);

        $escapedPath = escapeshellarg($filePath);
        $output = shell_exec("python {$tempPy} {$escapedPath} 2>&1");
        @unlink($tempPy);

        $data = json_decode($output, true);
        if (!is_array($data)) {
            throw new \Exception("Gagal membaca file Excel: " . substr($output, 0, 200));
        }

        return $data;
    }
}
