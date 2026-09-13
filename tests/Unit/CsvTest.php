<?php

namespace Tests\Unit;

use App\Support\Csv;
use PHPUnit\Framework\TestCase;

/**
 * Exports put member-typed text (names, comments) into files people open in
 * Excel, so a cell must never be able to run as a formula.
 */
class CsvTest extends TestCase
{
    public function test_text_that_looks_like_a_formula_is_neutralised(): void
    {
        $this->assertSame("'=HYPERLINK(\"x\")", Csv::cell('=HYPERLINK("x")'));
        $this->assertSame("'+1+1", Csv::cell('+1+1'));
        $this->assertSame("'-2+3", Csv::cell('-2+3'));
        $this->assertSame("'@SUM(A1)", Csv::cell('@SUM(A1)'));
    }

    public function test_ordinary_values_pass_through(): void
    {
        $this->assertSame('Fiona Goode', Csv::cell('Fiona Goode'));
        $this->assertSame(51.0, Csv::cell(51.0));
        $this->assertSame(-3, Csv::cell(-3));
        $this->assertSame('', Csv::cell(null));
        $this->assertSame('', Csv::cell(''));
    }
}
