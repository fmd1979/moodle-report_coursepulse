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
/** XLSX cell types, long durations and incomplete measurement handling. */
final class excel_test extends \advanced_testcase {
    /** Export actual XLSX bytes and reopen them to verify typed values and safe text. */
    public function test_xlsx_values_and_truncated_measurements(): void {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        $row = (object)['id' => 7, 'firstname' => '=SUM(1,1)', 'lastname' => 'Example',
            'progress' => 99.5, 'lastaccess' => 1234567890, 'risk' => 'active'];
        $roster = ['summary' => array_fill_keys(['total', 'active', 'warning', 'critical', 'never', 'completed'], 1),
            'rows' => [7 => $row]];
        $engagement = ['users' => [7 => ['seconds' => 90000, 'sessions' => 2, 'events' => 4]], 'truncated' => false];
        $course = (object)['id' => 2, 'fullname' => 'Synthetic course'];
        $filters = ['from' => 0, 'to' => 1234567890, 'risk' => 'All', 'search' => ''];
        $book = \report_coursepulse\local\excel::build($course, $roster, $engagement, $filters);
        $property = new \ReflectionProperty(\MoodleExcelWorkbook::class, 'objspreadsheet');
        $spread = $property->getValue($book);
        $this->assertSame(2, $spread->getSheetCount());
        $sheet = $spread->getSheet(1);
        $this->assertSame('s', $sheet->getCell('A2')->getDataType());
        $this->assertEqualsWithDelta(.995, $sheet->getCell('B2')->getValue(), .00001);
        $this->assertSame('0.0%', $sheet->getStyle('B2')->getNumberFormat()->getFormatCode());
        $this->assertEqualsWithDelta(90000 / DAYSECS, $sheet->getCell('E2')->getValue(), .00001);
        $this->assertSame('[h]:mm:ss', $sheet->getStyle('E2')->getNumberFormat()->getFormatCode());
        $path = $CFG->tempdir . '/coursepulse-test.xlsx';
        \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spread, 'Xlsx')->save($path);
        $loaded = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $this->assertSame('s', $loaded->getSheet(1)->getCell('A2')->getDataType());
        unlink($path);
        $engagement['truncated'] = true;
        $book = \report_coursepulse\local\excel::build($course, $roster, $engagement, $filters);
        $this->assertNull($property->getValue($book)->getSheet(1)->getCell('E2')->getValue());
    }
}
