<?php
// This file is part of Moodle - https://moodle.org/
// Moodle is free software: you can redistribute it and/or modify it under the
// terms of the GNU General Public License as published by the Free Software
// Foundation, either version 3 of the License, or (at your option) any later version.
// Moodle is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
// without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
/**
 * CoursePulse course engagement dashboard.
 *
 * @package report_coursepulse
 * @copyright 2026 Franklin David Moya Davila / SiteEcuador
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_coursepulse;
defined('MOODLE_INTERNAL') || die();
use report_coursepulse\local\pdf_export;
use report_coursepulse\local\excel;
/** Native PDF output and typed, injection-safe category spreadsheets. */
final class export_test extends \advanced_testcase {
    /** Synthetic data with markup, long names and an unavailable group-scoped course. */
    private function snapshot(): array {
        $summary = ['total' => 10, 'warning' => 2, 'critical' => 1, 'never' => 1, 'completed' => 3];
        return ['allcourses' => true, 'matched' => 2, 'totals' => $summary, 'rows' => [
            7 => ['course' => (object)['id' => 7, 'fullname' => '=SUM(1,1) & English'],
                'summary' => $summary, 'progress' => 62.5],
            8 => ['course' => (object)['id' => 8, 'fullname' => 'Separate groups'],
                'summary' => null, 'progress' => null],
        ]];
    }
    /** Percentages stay numeric and unavailable course counts never become misleading zeroes. */
    public function test_category_xlsx_types_and_scope(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $book = excel::build_overview((object)['id' => 1, 'name' => 'Synthetic category'], $this->snapshot(), 2);
        $property = new \ReflectionProperty(\MoodleExcelWorkbook::class, 'objspreadsheet');
        $spread = $property->getValue($book);
        $this->assertSame(2, $spread->getSheetCount());
        $this->assertSame(2, $spread->getSheet(0)->getCell('B2')->getValue());
        $sheet = $spread->getSheet(1);
        $this->assertSame('s', $sheet->getCell('B2')->getDataType());
        $this->assertEqualsWithDelta(.625, $sheet->getCell('D2')->getValue(), .00001);
        $this->assertSame('0.0%', $sheet->getStyle('D2')->getNumberFormat()->getFormatCode());
        $this->assertNull($sheet->getCell('C3')->getValue());
        $this->assertNull($sheet->getCell('D3')->getValue());
        $this->assertNotEmpty($sheet->getCell('I3')->getValue());
    }
    /** Malicious HTML in names must remain escaped text, never image resources or links. */
    public function test_pdf_cell_escaping(): void {
        $html = pdf_export::table(['Name'], [['&lt;img src="https://example.invalid/private"&gt; & Ana']], [100]);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;img', $html);
        $this->assertStringContainsString('&amp; Ana', $html);
    }
    /** Exercise real native TCPDF, including pagination and Unicode. */
    public function test_pdf_category_and_course_documents(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $data = $this->snapshot();
        $doc = pdf_export::overview((object)['id' => 1, 'name' => 'Categoría Ñ'], $data, 0);
        $this->assertStringStartsWith('%PDF-', $doc->Output('', 'S'));
        $summary = $data['totals'] + ['active' => 3, 'grace' => 0];
        $rows = [];
        for ($i = 1; $i <= 25; $i++) {
            $rows[$i] = (object)['id' => $i, 'firstname' => 'Estudiante María ' . $i,
                'lastname' => str_repeat('Apellido largo ', 4), 'progress' => 62.5, 'lastaccess' => 0, 'risk' => 'warning'];
        }
        $filters = ['from' => 0, 'to' => time(), 'risk' => 'All', 'search' => '', 'group' => 'All', 'page' => 1];
        $doc = pdf_export::course((object)['id' => 7, 'fullname' => 'Curso de Inglés'],
            ['summary' => $summary, 'rows' => $rows], ['users' => [], 'truncated' => true], $filters);
        $pages = $doc->getNumPages();
        $bytes = $doc->Output('', 'S');
        $this->assertStringStartsWith('%PDF-', $bytes);
        $this->assertStringContainsString('%%EOF', $bytes);
        $this->assertGreaterThan(1, $pages);
    }
}
